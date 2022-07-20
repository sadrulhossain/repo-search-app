
const SearchBox = ({onClickSearch, inputRef}) => {
  return (
    <div className="search-box">
      <input id="search" ref={inputRef} type="text" name="search" className="search" placeholder="Search for a repository ..."/>
      <button className="search-repo" onClick={onClickSearch}>Search</button>
    </div>
  )
}

export default SearchBox
