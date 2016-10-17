# Mirror git repository

chmod +x {{ wrapper_file }}
export GIT_SSH={{ wrapper_file }}

if [ ! -d {{ mirror_path }} ]; then
    git clone --mirror {{ repository }} {{ mirror_path }}
fi

cd {{ mirror_path }}

git fetch --all --prune
