<?php

use Illuminate\Support\Facades\DB;
use App\DailyProduct;
use App\ProductCheckInDetails;
use App\ProductConsumptionDetails;
use App\LotWiseConsumptionDetails;
use App\DailyProductDetails;
use App\Product;
use Illuminate\Support\Facades\Auth;
use App\Configuration;
use App\AclUserGroupToAccess;

class Helper {

    //function for back same page after update,delete,cancel
    public static function queryPageStr($qpArr) {
        //link for same page after query
        $qpStr = '';
        if (!empty($qpArr)) {
            $qpStr .= '?';
            foreach ($qpArr as $key => $value) {
                if ($value != '') {
                    $qpStr .= $key . '=' . $value . '&';
                }
            }
            $qpStr = trim($qpStr, '&');
            return $qpStr;
        }
    }

    public static function printDate($date = '0000-00-00') {
        return date('F jS, Y', strtotime($date));
    }

    public static function printDateFormat($date = '0000-00-00') {
        return date('d F Y \a\t g:i a', strtotime($date));
    }

    public static function getEventTypeArr() {
        $eventTypeArr = ['1' => __('label.EVENT'), '2' => __('label.CONSIDERATION')];
        return $eventTypeArr;
    }

    // public static function getMonthArr() {
    // $eventTypeArr = ['1' => __('label.EVENT'), '2' => __('label.CONSIDERATION')];
    // return $eventTypeArr
    // }
//function for getOrderList
    public static function getOrderList($model = null, $operation = null, $parentId = null, $parentName = null) {

        /*
         * Operation :: 1 = Create, 2= Edit
         */
        $namespacedModel = '\\App\\' . $model;
        $targetArr = $namespacedModel::select(array(DB::raw('COUNT(id) as total')));
        if (!empty($parentId)) {
            $targetArr = $targetArr->where($parentName, $parentId);
        }
        $targetArr = $targetArr->first();
        $count = $targetArr->total;

        //in case of Create, always Increment the number of element in order 
        //to accomodate new Data
        if ($operation == '1') {
            $count++;
        }
        return array_combine(range(1, $count), range(1, $count));
    }

    //function for Insert order
    public static function insertOrder($model = null, $order = null, $id = null, $parentId = null, $parentName = null) {
        $namespacedModel = '\\App\\' . $model;
        $namespacedModel::where('id', $id)->update(['order' => $order]);
        $target = $namespacedModel::where('id', '!=', $id)->where('order', '>=', $order);
        if (!empty($parentId)) {
            $target = $target->where($parentName, $parentId);
        }
        $target = $target->update(['order' => DB::raw('`order`+ 1')]);
    }

    // function for Update Order
    public static function updateOrder($model = null, $newOrder = null, $id = null, $presentOrder = null, $parentId = null, $parentName = null) {
        $namespacedModel = '\\App\\' . $model;
        $namespacedModel::where('id', $id)->update(['order' => $newOrder]);

        //condition for order range
        $target = $namespacedModel::where('id', '!=', $id);
        if (!empty($parentId)) {
            $target = $target->where($parentName, $parentId);
        }

        if ($presentOrder < $newOrder) {
            //$namespacedModel::where('id', '!=', $id)->where('order', '>=', $presentOrder)->where('order', '<=', $newOrder)->update(['order' => DB::raw('`order`- 1')]);
            $target = $target->where('order', '>=', $presentOrder)->where('order', '<=', $newOrder)->update(['order' => DB::raw('`order`- 1')]);
        } else {
            $target = $target->where('order', '>=', $newOrder)->where('order', '<=', $presentOrder)->update(['order' => DB::raw('`order`+ 1')]);
        }
    }

    public static function deleteOrder($model = null, $order = null, $parentId = null, $parentName = null) {
        $namespacedModel = '\\App\\' . $model;
        $target = $namespacedModel::where('order', '>=', $order);
        if (!empty($parentId)) {
            $target = $target->where($parentName, $parentId);
        }

        $target = $target->update(['order' => DB::raw('`order`- 1')]);
    }

    public static function getLastOrder($model = null, $operation = null, $parentId = null, $parentName = null) {

        /*
         * Operation :: 1 = Create, 2= Edit
         */
        $namespacedModel = '\\App\\' . $model;
        $targetArr = $namespacedModel::select(array(DB::raw('COUNT(id) as total')));
        if (!empty($parentId)) {
            $targetArr = $targetArr->where($parentName, $parentId);
        }
        $targetArr = $targetArr->first();

        $count = $targetArr->total;

//in case of Create, always Increment the number of element in order 
//to accomodate new Data
        if ($operation == '1') {
            $count++;
        }

        return $count;
    }

    public static function numberformat($num = 0, $digit = 3) {
        return number_format($num, $digit, '.', ',');
    }

    public static function printDateTime($date = '0000-00-00 00:00:00') {
        return date('d/m/y H:i', strtotime($date));
    }

    public static function printOnlyDate($date = '0000-00-00') {
        return date('d/m/y', strtotime($date));
    }

    //For make Print any data
    public static function pr($data, $number) {
        echo "<pre>";
        print_r($data);
        if ($number == '1') {
            return exit;
        } else {
            return false;
        }
    }

    public static function dateFormat($date = '0000-00-00') {
        return date('d/m/Y', strtotime($date));
    }

    public static function unitConversion($totalQtyStr = "") {
        $pos = strpos($totalQtyStr, ".");
        if ($pos === false) {
            $kgAmnt = $totalQtyStr;
            $gmAmntArr = "";
        } else {
            $totalQtyArr = explode(".", $totalQtyStr);
            $kgAmnt = $totalQtyArr[0];
            $gmAmntArr = $totalQtyArr[1];
        }

        $kgFinalAmntStr = '';
        if ($kgAmnt > 0) {
            $kgFinalAmntStr = (int) $kgAmnt . " " . __('label.UNIT_KG');
        }


        if ($pos !== false) { //If decimal point exists
            $totalAmntStr = str_pad($gmAmntArr, 6, "0", STR_PAD_RIGHT);

            $gmStr = substr($totalAmntStr, 0, 3); //Subtract gram aamount
            $gmFinalAmntStr = "";
            if ($gmStr > 0) {
                $gmFinalAmntStr = (int) $gmStr . " " . __('label.GM');
            }
            $miliGmStr = substr($totalAmntStr, 3, 3); //Subtract miligram aamount
            $mgFinalAmntStr = "";
            if ($miliGmStr > 0) {
                $mgFinalAmntStr = (int) $miliGmStr . " " . __('label.MG');
            }

            $qtyTotalDetail = $kgFinalAmntStr . " " . $gmFinalAmntStr . " " . $mgFinalAmntStr;
        } else {
            $qtyTotalDetail = $kgFinalAmntStr;
        }

        return $qtyTotalDetail;
    }

    public static function getAccessList() {
        //Get User Group Access
        $userGroupToAccessArr = AclUserGroupToAccess::select('acl_user_group_to_access.module_id', 'acl_user_group_to_access.access_id')
                        ->where('acl_user_group_to_access.group_id', '=', Auth::user()->group_id)
                        ->orderBy('acl_user_group_to_access.module_id', 'asc')
                        ->orderBy('acl_user_group_to_access.access_id', 'asc')->get();

        //echo '<pre>';print_r($userGroupToAccessArr->toArray());exit;
        //User_group_Module_to_Access Table
        if (!$userGroupToAccessArr->isEmpty()) {
            foreach ($userGroupToAccessArr as $ma) {
                $moduleToGroupAccessListArr[$ma->module_id][] = $ma->access_id;
            }
        }


        $value = "Hello";
        //session_start();
        //$_SESSION['variableName'] =  $value;
        //echo Session::get('variableName');
        //exit;
        Session::put('moduleToGroupAccessListArr', $moduleToGroupAccessListArr);
        //echo '<pre>';print_r(Session::get('variableName'));
    }

    public static function formatDate($dateTime = '0000-00-00 00:00:00') {
        $formatDate = !empty($dateTime) ? date('d F Y', strtotime($dateTime)) : '';
        return $formatDate;
    }

    public static function formatDateTime($dateTime = '0000-00-00 00:00:00') {
        $formatDate = !empty($dateTime) ? date('d F Y h:i A', strtotime($dateTime)) : '';
        return $formatDate;
    }

    public static function getMachineType() {
        $machineTypeArr = ['1' => __('label.MANUAL'), '2' => __('label.AUTOMATIC')];
        return $machineTypeArr;
    }

    public static function getCustomerType() {
        $machineTypeArr = ['1' => __('label.BONDED'), '2' => __('label.COMMERCIAL')];
        return $machineTypeArr;
    }

    public static function arrayToString($array = []) {
        $string = '';
        if (!empty($array)) {
            $string = implode(',', $array);
        }
        return $string;
    }

    public static function stringToArray($string = null) {
        $array = [];
        if (!empty($string)) {
            $array = explode(',', $string);
        }
        return $array;
    }

    //new function
    public static function dateFormatConvert($date = '0000-00-00') {
        return date('Y-m-d', strtotime($date));
    }

    public static function numberFormat2Digit($num = 0) {
        if (empty($num)) {
            $num = 0;
        }
        return number_format($num, 3, '.', ',');
    }

    public static function numberFormatDigit2($num = 0) {
        if (empty($num)) {
            $num = 0;
        }
        return number_format($num, 3, '.', '');
    }
    public static function numberFormat3Digit($num = 0) {
        if (empty($num)) {
            $num = 0;
        }
        return number_format($num, 3, '.', ',');
    }

    public static function numberFormatDigit3($num = 0) {
        if (empty($num)) {
            $num = 0;
        }
        return number_format($num, 3, '.', '');
    }

    public static function daySpan($timeSpan) {
        $days = ' Day';
        if ($timeSpan > 1) {
            $days = ' Days';
        }
        return $days;
    }

    public static function trimString($string) {
        $string = strip_tags($string);
        $dot = strlen($string) > 20 ? '...' : '';
        $returnString = substr($string, 0, 20) . $dot;
        return $returnString;
    }
    public static function trimString100($string) {
        $string = strip_tags($string);
        $dot = strlen($string) > 100 ? '...' : '';
        $returnString = substr($string, 0, 100) . $dot;
        return $returnString;
    }

    public static function numberToWord($number = null) {

        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                    'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . self::numberToWord(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . self::numberToWord($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::numberToWord($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::numberToWord($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public static function getUrlRequestText($url) {
        $urlTextArr = explode("?", $url);
        $urlRequestText = !empty($urlTextArr[1]) ? '?' . $urlTextArr[1] : '';
        return $urlRequestText;
    }

    public static function numberToOrdinal($number = null) {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if (!empty($number)) {
            if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                return $number . 'th';
            } else {
                return $number . $ends[$number % 10];
            }
        } else {
            return '';
        }
    }

    public static function dateDiff($startDate, $endDate) {
        $startDateTime = date_create($startDate);
        $endDateTime = date_create($endDate);

        $interval = date_diff($startDateTime, $endDateTime);

        $format = '';
        if (!empty($interval)) {
            if (!empty($interval->y)) {
                if ($interval->y > 1) {
                    if (!empty($interval->m)) {
                        if ($interval->m > 1) {
                            if (!empty($interval->d)) {
                                if ($interval->d > 1) {
                                    $format = '%y Years %m Months %d Days';
                                } else {
                                    $format = '%y Years %m Months %d Day';
                                }
                            } else {
                                $format = '%y Years %m Months';
                            }
                        } else {
                            if (!empty($interval->d)) {
                                if ($interval->d > 1) {
                                    $format = '%y Years %m Month %d Days';
                                } else {
                                    $format = '%y Years %m Month %d Day';
                                }
                            } else {
                                $format = '%y Years %m Month';
                            }
                        }
                    } else {
                        if (!empty($interval->d)) {
                            if ($interval->d > 1) {
                                $format = '%y Years %d Days';
                            } else {
                                $format = '%y Years %d Day';
                            }
                        } else {
                            $format = '%y Years';
                        }
                    }
                } else {
                    if (!empty($interval->m)) {
                        if ($interval->m > 1) {
                            if (!empty($interval->d)) {
                                if ($interval->d > 1) {
                                    $format = '%y Year %m Months %d Days';
                                } else {
                                    $format = '%y Year %m Months %d Day';
                                }
                            } else {
                                $format = '%y Year %m Months';
                            }
                        } else {
                            if (!empty($interval->d)) {
                                if ($interval->d > 1) {
                                    $format = '%y Year %m Month %d Days';
                                } else {
                                    $format = '%y Year %m Month %d Day';
                                }
                            } else {
                                $format = '%y Year %m Month';
                            }
                        }
                    } else {
                        if (!empty($interval->d)) {
                            if ($interval->d > 1) {
                                $format = '%y Year %d Days';
                            } else {
                                $format = '%y Year %d Day';
                            }
                        } else {
                            $format = '%y Year';
                        }
                    }
                }
            } else {
                if (!empty($interval->m)) {
                    if ($interval->m > 1) {
                        if (!empty($interval->d)) {
                            if ($interval->d > 1) {
                                $format = '%m Months %d Days';
                            } else {
                                $format = '%m Months %d Day';
                            }
                        } else {
                            $format = '%m Months';
                        }
                    } else {
                        if (!empty($interval->d)) {
                            if ($interval->d > 1) {
                                $format = '%m Month %d Days';
                            } else {
                                $format = '%m Month %d Day';
                            }
                        } else {
                            $format = '%m Month';
                        }
                    }
                } else {
                    if (!empty($interval->d)) {
                        if ($interval->d > 1) {
                            $format = '%d Days';
                        } else {
                            $format = '%d Day';
                        }
                    } else {
                        $format = '%d Day';
                    }
                }
            }
        }
        return $interval->format($format);
    }

    public static function getMaritalStatus() {
        $statusArr = ['1' => __('label.MARRIED'), '2' => __('label.UNMARRIED'), '3' => __('label.DIVORCED'), '4' => __('label.WIDOW')];
        return $statusArr;
    }

    public static function getSwimming() {
        $statusArr = ['1' => __('label.SWIMMER'), '2' => __('label.WEAK_SWIMMER'), '3' => __('label.NON_SWIMMER')];
        return $statusArr;
    }

}
