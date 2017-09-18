# Create shared directories

echo -e "Create shared directory from {{ source_file }} to {{ target_file }}"

if [ -d {{ target_file }} ]; then
    if [ ! -d {{ source_file }} ]; then
        cp -pRn {{ target_file }} {{ source_file }}
    fi
    rm -rf {{ target_file }}
fi

if [ ! -d {{ source_file }} ]; then
    mkdir {{ source_file }}
fi

ln -s {{ source_file }} {{ target_file }}
