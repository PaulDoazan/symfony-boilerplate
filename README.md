# symfony-boilerplate
Symfony 5.4.7 with basic user admin

## settings

lando console composer self-update <br />
lando console composer install <br />
lando console npm install <br />
lando console npm run build <br />
lando console doc:mig:mig <br />

.env.local : DATABASE_URL="mysql://symfony:symfony@database:3306/symfony?serverVersion=mariadb-10.4.0"



Documentation pour installation environnement Lando dans WSL :
https://gist.github.com/quentint/27d3aef3d7b359c6e416eba6f9628664

# Lando with Hyperdrive

On Windows, make sure you're ready for WSL2: https://docs.lando.dev/guides/setup-lando-on-windows-with-wsl-2.html

Open an Ubuntu terminal, then:

## Install Docker

https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository

## Install and run Hyperdrive

```sh
curl -Ls https://github.com/lando/hyperdrive/releases/download/v0.6.1/hyperdrive > /tmp/hyperdrive
chmod +x /tmp/hyperdrive
/tmp/hyperdrive
```

## Update Lando

```sh
wget https://github.com/lando/lando/releases/download/v3.6.2/lando-x64-v3.6.2.deb
sudo dpkg -i lando-x64-v3.6.2.deb
rm lando-x64-v3.6.2.deb
```

## Initialize Symfony project

```sh
cd ~
mkdir my-new-project && cd my-new-project
lando composer self-update
lando init
lando composer create-project symfony/skeleton:^6.0 tmp
cd tmp
lando composer require webapp
cd ..
cp -r tmp/. . && rm -rf tmp
```

## Start Lando

```sh
lando start
```
