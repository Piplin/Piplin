# Generate public SSH key

ssh-keygen -y -f {{ key_file }} | xargs echo -n > {{ key_file }}.pub
echo " worker@piplin" >> {{ key_file }}.pub
