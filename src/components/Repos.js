import Repo from "./Repo.js";

const Repos = ({ repos }) => {
  return (
    <div className="repos">
      {repos.length > 0 ? (
        repos.map((repo, index) => 
        <Repo repo = {repo} key = {index}/>)
      ) : (
        <div className="repo">
          <span className="repo-desc">No repository found or no repository is being searched.</span>
        </div>
      )}
    </div>
  );
};

export default Repos;
