# Purge old releases

cd {{ releases_path }}

(ls -t|head -n {{ builds_to_keep }};ls)|sort|uniq -u|xargs rm -rf
