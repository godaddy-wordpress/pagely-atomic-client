# atomic-client
PHP Library to access the Pagely atomic APIs

*Note that PHP 7.1+ is required*

# Installation

Installation assumes you already have git, PHP7.1+, and composer installed.

```bash
git clone git@github.com:pagely/atomic-client.git
cd atomic-client/
composer install
```

Optionally, enable tab-completion and add the `atomic` client to your `$PATH`:

```bash
./bin/atomic _completion --generate-hook --program atomic >> ~/.bashrc
echo "PATH=$PWD/bin:\$PATH" >> ~/.bashrc
```

# Using the client
## Login
```bash
atomic auth:login my@atomic-username.com
```

This will save a login token to `~/.atomiclogin`. The login will be good for 14 hours.  After that you will need to login again.
This file will automatically be read by all other commands and used for authorization
to the Pagely API.

You may also use your API keys to log in using the `auth:client-login` command:

```bash
atomic auth:client-login <clientId> <clientSecret>
```

When done, you may use the `auth:logout` command or simply remove the `~/.atomiclogin` file.

## Commands

Executing the `atomic` command by itself will show the commands available.
```bash
atomic
```

To get usage help for any command, simply prefix the command name with `help`
```bash
atomic help auth:login
```

## Help!

### What is my Account ID?
1. You can get your account ID by logging into https://atomic-beta.pagely.com and
looking at the address in your browser. Your account ID will be the number directly following `/account/` in the address.

2. If you are a collaborator and need the ID for another account, log in to atomic-beta (link above) and use the account switcher
(click your name in the upper right) and switch to the account in question. The address in your browser
will change to reflect the account ID you are now looking at.
