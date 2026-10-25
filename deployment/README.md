# deployment

## Dev

You can easily mount:
- A mariadb server for dev environment
- A mariadb server for test environment
- An adminer instance

To do this:
- Step 1: Create deployment/docker/.env file based on deployment/docker/.env.example
- Step 2: Go to deployment/docker and run the following command

```
docker-compose up -d
```

Dev tools are now accessible here:
- Adminer: localhost:5001

## Prod

### Requirement

This repo does not include server provisioning but you can refer to [this other repo](https://github.com/mp3000mp/template-ansible) if you like.

This hosting server needs the following requirements to deploy:
- rsync
- docker
- docker compose
- apache (modules enabled by `apache.yml`)
- certbot

The machine you deploy from needs ansible, rsync, docker and node: the backend `vendor/`, the frontend `dist/` and the dw binary are built locally (backend and dw in docker), then shipped.


### Deployment

Prod deployment include:
- Deployment of code source (backend vendor/ built without dev dependencies)
- A mariadb server
- Start of dw service
- Maintenance page on the front during the deployment window

Setup:
- Step 1: Create your inventory file in deployment/ansible/inventory/hosts
- Step 2: Create deployment/ansible/vars.yml file based on deployment/ansible/vars.example.yml
- Step 3 (first deployment, or after a vhost change): install the apache vhosts and Let's Encrypt certificates

```
cd deployment/ansible
ansible-playbook apache.yml
```

Then deploy (builds what is needed locally first, see `local_build.sh`):

```
ansible-playbook site.yml                   # everything
ansible-playbook site.yml --tags backend    # backend code + vendor, rebuild/restart containers, migrations
ansible-playbook site.yml --tags frontend   # frontend dist only
ansible-playbook site.yml --tags dw         # dw binary, restart containers
ansible-playbook site.yml --tags infra      # compose, Dockerfiles, php.ini, rebuild/restart containers
ansible-playbook site.yml --tags backup     # backup script and cron (never run by default)
```

If a deployment fails midway, the front stays in maintenance: fix and redeploy, or remove `<app_directory>/frontend/maintenance.on`.
Set `maintenance_mode: true` in vars.yml to keep the site closed after a deployment.
