# TM Deployer

Make package dempoyment tool using deployer, magento-composer-installer

### Usage

```sh
$ cd [deployer dir]
$ dep deploy  --package="tm/ajaxsearch:*"
$ ls bin
```

### Installation

1. Install Composer
    Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

    ```sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

    > **Note:** If the above fails for some reason, you can download the installer
    > with `php` instead:

    ```sh
    php -r "readfile('https://getcomposer.org/installer');" | php
    ```

2. Install Deployer
    To install Deployer download [deployer.phar](http://deployer.org/deployer.phar) archive and move deployer.phar to your bin
    directory and make it executable.

    ```sh
    $ curl -L http://deployer.org/deployer.phar -o deployer.phar
    $ mv deployer.phar /usr/local/bin/dep
    $ chmod +x /usr/local/bin/dep
    ```

3. Download and install jq

    ```sh
    $ sudo apt-get install jq
    ```
    or
    ```sh
    $ chocolatey install jq
    ```
    or
    ```sh
    $ git clone https://github.com/stedolan/jq.git
    $ cd jq
    $ autoreconf -i
    $ ./configure --disable-maintainer-mode
    $ make
    $ sudo make install
    ```

4. Downoad tmhub/deployer

    ```sh
    $ git clone git@github.com:tmhub/deployer.git
    $ cd deployer
    ```
