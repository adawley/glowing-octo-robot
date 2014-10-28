remove file from history
========================
replace \<\<directory or file\>\> 

directory needs to use \* (i.e. /path/to/sub/dir/* )

    git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch <<directory or file>>' --prune-empty --tag-name-filter cat -- --all
