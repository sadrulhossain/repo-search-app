import { useState, useRef } from "react";
import SearchBox from "./components/SearchBox.js";
import Repos from "./components/Repos.js";

const App = () => {
  const inputRef = useRef(null);
  const [repos, setRepos] = useState([]);
  const onClickSearch = () => {
    let searchText = inputRef.current.value;

    const getRepos = async (searchText) => {
      const res = await fetch(
        "http://localhost/repoSearchApi/api/getRepos?search="+searchText
      );
      const data = await res.json();
      const repositories = data.repositories;
      setRepos(repositories);
    };
    getRepos(searchText);
  };

  return (
    <div className="container">
      <SearchBox onClickSearch={onClickSearch} inputRef={inputRef} />
      <Repos repos={repos} />
    </div>
  );
};

export default App;
