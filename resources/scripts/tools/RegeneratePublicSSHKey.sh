ssh-keygen -y -f {{ key_file }} | xargs echo -n > {{ key_file }}.pub
echo " deploy@fixhub" >> {{ key_file }}.pub
