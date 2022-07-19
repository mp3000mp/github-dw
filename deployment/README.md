# deployment

## Dev

You can easily mount:
- A mariadb server for dev environment
- A mariadb server for test environment
- A maildev server
- An adminer instance

To do this:
- Step 1: Create deployment/docker/.env file based on deployment/docker/.env.example
- Step 2: Go to deployment/docker and run the following command

```
docker-compose up -d
```

Dev tools are now accessible here:
- Maildev: localhost:1080
- Adminer: localhost:5001

## Prod

### Requirement

This repo does not include server provisioning but you can refer to [this other repo](https://github.com/mp3000mp/template-ansible) if you like.

This hosting server needs the following requirements to deploy:
- python3-pip (for Ansible Docker module compatibility)
- rsync
- docker
- docker-compose
- nodejs >16.0
- apache with proxy and proxy_http modules
- certbot


### Deployment

Prod deployment include:
- Deployment of code source
- A mariadb server
- Start of dw service

You can easily deploy the application:
- Step 1: Create your inventory file in deployment/ansible/inventory/hosts
- Step 2: Create deployment/ansible/vars.yml file based on deployment/ansible/vars.example.yml
- Step 3: Go to deployment/ansible and run the following command

```
./local_build.sh
ansible-playbook -i inventory/hosts site.yml
```
