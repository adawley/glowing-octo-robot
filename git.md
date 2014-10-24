remove file from history
========================
replace <<directory or file>> 
directory needs to use * so /path/to/sub/dir/*

  git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch <<direcotry or file>>' --prune-empty --tag-name-filter cat -- --all
