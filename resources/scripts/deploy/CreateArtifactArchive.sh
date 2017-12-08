# Create artifact archive

echo -e "Create artifact archive"

cd {{ artifact_path }}

(tar zcvf {{ release_archive }} {{ files }})
