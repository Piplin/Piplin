# Result

echo -e "Result {{ build_path }}"

cd {{ builds_path }}

(ls -t|head -n {{ builds_to_keep }};ls)|sort|uniq -u|xargs rm -rf
