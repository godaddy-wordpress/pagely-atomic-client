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
## Authentication

You can authenticate either as your Atomic user _or_ with an API client key.

### Authenticating as your Atomic user

```bash
atomic auth:login my@atomic-username.com
```

This saves an auth token to `~/.atomiclogin`.

### Authenticating with an API key

```bash
atomic auth:client-login <clientId> <clientSecret>
```

This saves a token to `~/.atomicclientlogin`.

---

Regardless of how you authenticate, when you are done you can use the `auth:logout` command or simply remove the `~/.atomiclogin`/`~/.atomicclientlogin` file.

**NOTE:** If both are present, Atomic user tokens take precedence over API client tokens. If you have a user token you need to run `auth:logout` or manually remove the `~/.atomiclogin` file before you can run commands with your API client credentials.

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
1. You can get your account ID by logging into https://atomic.pagely.com and
looking at the address in your browser. Your account ID will be the number directly following `/account/` in the address.

2. If you are a collaborator and need the ID for another account, log in to Atomic (link above) and use the account switcher
(click your name in the upper right) and switch to the account in question. The address in your browser
will change to reflect the account ID you are now looking at.
