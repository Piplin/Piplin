# Ansible example

Here's an example of an Ansible set up the fixhub tool in a server with Ubuntu Server installed.

## Set up variables

 - In the hosts file change the IP address and replace it with your production server.

 - In the group_vars/production.yml file customize the vhost domain

 - In the vars/server.yml file you can change what version of the fixhub is going to be installed

## Launch ansible

```
ansible-playbook -i hosts playbook.yml --limit colt
```
