<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RepoSearchController extends Controller {

    public function index(Request $request) {
        //get all repo from api
        $allRepoInfo = $this->httpRepoHandler('search/repositories', 'GET');

        $allRepoArr = json_decode($allRepoInfo, true);

        $search = $request->search ?? '';
        $i = 0;
        $repositories = [];
        if (!empty($allRepoArr['items'])) {
            foreach ($allRepoArr['items'] as $index => $info) {
                //check if the name if close to search text 
                if ($i < 20) {
                    if (!empty($info['name']) && !empty($search) && preg_match("/" . $search . "/i", $info['name'])) {
                        $repositories[$i]['name'] = $info['name'] ?? '';
                        $repositories[$i]['author'] = $info['owner']['login'] ?? '';
                        $repositories[$i]['description'] = $info['description'] ?? '';
                        $repositories[$i]['updatedAt'] = !empty($info['updated_at']) ? date("M d, Y", strtotime($info['updated_at'])) : '';
                        $repositories[$i]['language'] = $info['language'] ?? '';

                        //find out top contributor
                        $repoCont = $this->getTopContributor($info['owner']['login'], $info['name']);

                        $repositories[$i]['topContributorUsername'] = $repoCont['cont'] ?? '';
                        $repositories[$i]['topContributorAdditions'] = $repoCont['a'] ?? 0;
                        $repositories[$i]['topContributorDeletions'] = $repoCont['d'] ?? 0;
                        $repositories[$i]['topContributorCommits'] = $repoCont['c'] ?? 0;


                        $repositories[$i]['url'] = $info['url'] ?? '';

                        $i++;
                    }
                }
            }
        }

        return response()->json(['repositories' => $repositories]);
    }

    public function getTopContributor($owner, $repo) {
        $repoInfo = $this->httpRepoHandler('repos/' . $owner . '/' . $repo . '/stats/contributors', 'GET');
        $repoArr = json_decode($repoInfo, true);

        $contWiseRepoArr = $repoContArr = [];
        if (!empty($repoArr)) {
            foreach ($repoArr as $index => $repo) {
                $totalCont = $repo['weeks']['a'] + $repo['weeks']['d'] + $repo['weeks']['c'];
                $contWiseRepoArr[$totalCont][$index] = $repo['author']['login'];
                $repoContArr[$repo['author']['login']]['cont'] = $repo['author']['login'];
                $repoContArr[$repo['author']['login']]['d'] = $repo['weeks']['d'];
                $repoContArr[$repo['author']['login']]['a'] = $repo['weeks']['a'];
                $repoContArr[$repo['author']['login']]['c'] = $repo['weeks']['c'];
            }

            $cont = reset(krsot(reset(krsort($contWiseRepoArr))));
            $repoCont = $repoContArr[$cont];
        }

        return $repoCont;
    }

    public static function httpRepoHandler($url, $rType) {

        $service_url = env('GIT_API_URL') . '/' . $url;
        $curl = curl_init($service_url);
        $headers = array(
            'Accept: application/vnd.github+json',
            'Authorization:  token ' . env('GIT_API_CLIENT_SECRET')
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $rType);
        //Set the headers that we want our cURL client to use.
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); // Default get method


        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $err = curl_error($curl);  //if you need
        curl_close($curl);
        return json_decode($curl_response, true);
    }

    //
}
