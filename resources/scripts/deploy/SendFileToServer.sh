rsync --verbose --compress --progress --out-format="Receiving %n" -e \
    "ssh -p {{ port }} \
         -o CheckHostIP=no \
         -o IdentitiesOnly=yes \
         -o StrictHostKeyChecking=no \
         -o PasswordAuthentication=no \
         -o IdentityFile={{ private_key }}" \
    {{ local_file }} {{ username }}@{{ ip_address }}:{{ remote_file }}
