const Repo = ({ repo }) => {
  return (
    <div className="repo">
      <table>
        <tr>
          <td>
            <span className="repo-title">{repo.name}</span>
            <br />
            <span className="repo-author">{repo.author}</span>
            <br />
            <span className="repo-desc">{repo.description}</span>
          </td>
          <td>
            <span className="repo-cont">Most Active Contributor :</span>&nbsp;
            <span className="repo-cont-un">{ repo.topContributorUsername }</span>
            <br />
            <span className="repo-activity-count">{ repo.topContributorAdditions }</span>&nbsp;
            <span className="repo-activity">additions</span>&nbsp;&nbsp;
            <span className="repo-activity-count">{ repo.topContributorDeletions }</span>&nbsp;
            <span className="repo-activity">deletions</span>&nbsp;&nbsp;
            <span className="repo-activity-count">{ repo.topContributorCommits }</span>&nbsp;
            <span className="repo-activity">commits</span>
          </td>
        </tr>
        <tr>
          <td>
            <span className="repo-lang">{ repo.language }</span>
          </td>
          <td className="repo-update-date">
            <span className="repo-last-update">Updated on { repo.updatedAt }</span>
          </td>
        </tr>
      </table>
    </div>
  );
};

export default Repo;
