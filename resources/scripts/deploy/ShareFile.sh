# Create shared files

if [ -f {{ target_file }} ]; then
    cp -pRn {{ target_file }} {{ source_file }}
    rm -rf {{ target_file }}
fi

if [ ! -f {{ source_file }} ]; then
    touch {{ source_file }}
fi

ln -s {{ source_file }} {{ target_file }}
