# Generate SSH Key

if [ -f {{ key_file }} ]; then
    rm -f {{ key_file }}
fi

ssh-keygen -q -t rsa -b 2048 -f {{ key_file }} -N "" -C "worker@piplin"
