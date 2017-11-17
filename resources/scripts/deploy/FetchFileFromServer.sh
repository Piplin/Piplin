# Fetch file from server

echo -e "Fetch file from server"

rsync --verbose --compress --progress --out-format="Receiving %n" -e \
    "ssh -p {{ port }} \
         -o CheckHostIP=no \
         -o IdentitiesOnly=yes \
         -o StrictHostKeyChecking=no \
         -o PasswordAuthentication=no \
         -o IdentityFile={{ private_key }}" \
    {{ username }}@{{ ip_address }}:{{ remote_file }} {{ local_file }}
