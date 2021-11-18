{
    "name": "hakrich_team/{appname}",
    "description": "",
    "type": "project",
    "homepage": "https://hakrichteam.com",
    "license": "MIT",
    "authors": [{
        "name": "Admin",
        "email": "{appname}@hakrichteam.com"
    }],
    "require": {},
    "require-dev": {},
    "autoload": {
        "psr-4": {
            "{AppNamespace}\\": "./App",
            "{VezirionNamespace}\\": "./Bin/"
        },
        "exclude-from-classmap": [
            "**/Database/Migrations/**"
        ]
    },
    "suggest": {
        "ext-fileinfo": "Improves mime type detection for files"
    },

    "scripts": {
        "test": "phpunit",
        "post-install-cmd": "{VezirionNamespace}\\Boot\\Installer::postInstall"
    }
}
