### Get commit details - {{ deployment }}
cd {{ mirror_path }}

git log {{ git_reference }} -n1 --pretty=format:"%H%x09%an%x09%ae"
