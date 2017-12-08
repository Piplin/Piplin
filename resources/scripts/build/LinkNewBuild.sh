# Link new build

echo -e "Link new build {{ build_path }}"

cd {{ project_path }}

# Remove the symlink if it already exists
if [ -h {{ project_path }}/build ]; then
    rm -f {{ project_path }}/build
fi

# Create the new symlink
ln -s {{ build_path }} {{ project_path }}/build
