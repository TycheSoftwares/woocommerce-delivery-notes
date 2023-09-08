github.dismiss_out_of_range_messages

warn "This PR is quite big! It contains over 5000 lines of code. Consider splitting it into separate PRs." if git.lines_of_code > 5000

failure "Please provide a summary in the PR description textfield" if github.pr_body.length < 2

failure "This PR does not have any assignee. Please assign yourself if you are the author of this PR." unless github.pr_json["assignee"]

failure "This PR has not been assigned to a reviewer. Every PR must be reviewed by a Senior Developer @ Tyche Softwares." unless github.pr_json["reviewer"]

failure "This PR has not been assigned to a milestone." unless github.pr_json["milestone"]

if git.modified_files.empty? && git.added_files.empty? && git.deleted_files.empty?
  failure "This PR has no changes at all, this is likely an issue during development."
end

# Verify if PR title contains issue numbers
fix = github.pr_body.scan(/\[(\w{1,3} #\d+)\]/)
if fix.empty?
  failure "This PR does not have any issue number in the description. (e.g. Fix #10)"
end

if github.pr_body.include? "do-not-scan"
  failure "Skipping of PHPCS Scan is highly discouraged."
end

commit_lint.check warn: :all

todoist.message = "There are still some things to do in this PR."
todoist.warn_for_todos
todoist.print_todos_table
