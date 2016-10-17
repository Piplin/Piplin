# Generate SSH Key

ssh-keygen -t rsa -b 2048 -f {{ key_file }} -N "" -C "deploy@fixhub"
