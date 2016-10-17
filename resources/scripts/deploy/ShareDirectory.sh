# Create shared directories

if [ -d {{ target_file }} ]; then
    cp -pRn {{ target_file }} {{ source_file }}
    rm -rf {{ target_file }}
fi

if [ ! -d {{ source_file }} ]; then
    mkdir {{ source_file }}
fi

ln -s {{ source_file }} {{ target_file }}
