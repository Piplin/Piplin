# Remove mirror directory

if [ -d {{ mirror_path }} ]; then
    rm -rf {{ {{ mirror_path }} }}
fi
