<@xml version="1.0" encoding="ISO-8859-15"?>
<Pobohets>
        <Pobohet xmlns="http://example/Default" method="index" namespace="APP_NAMESPACE\Controllers\" CONSTRAINT="any" controller="\Home" pobohets="*=[]'-,-'options=[]'-,-'get=[]'-,-'head=[]'-,-'put=[]'-,-'post=[]'-,-'delete=[]'-,-'trace=[]'-,-'connect=[]'-,-'cli=[]" placeholders="any=.*'-,-'all=*'-,-'segment=[^/]+'-,-'alphanum=[a-zA-Z0-9]+'-,-'num=[0-9]+'-,-'alpha=[a-zA-Z]+" HTTPMethods="options'-,-'get'-,-'head'-,-'put'-,-'post'-,-'delete'-,-'trace'-,-'connect'-,-'cli"/>
        <Pobohet xmlns="http://example/All" url="migrations/(:segment)/(:segment)" pobohet="cli" method="$1/$2" controller="\Hkm_code\Commands\MigrationsCommand"/>
        <Pobohet xmlns="http://example/All" url="BaseController(:any)" pobohet=":all" method="FOR_PAGE_NOT_FOUND" controller="\Hkm_code\Exceptions\PageNotFoundException"/>
        <Pobohet xmlns="http://example/All" url="(:any)/initController" pobohet=":all" method="FOR_PAGE_NOT_FOUND" controller="\Hkm_code\Exceptions\PageNotFoundException"/>
        <Pobohet xmlns="http://example/All" url="info/(:segment)" pobohet="get" method="info/$1" controller="Home"/> 
        <Pobohet xmlns="http://example/All" url="migrations/(:segment)" pobohet="cli" method="$1" controller="\Hkm_code\Commands\MigrationsCommand"/>
        <Pobohet xmlns="http://example/All" url="shorten_url/(:segment)/(:segment)" pobohet="get'-,-'post" method="$1/$2" controller="ShortenUrl" options="namespace=\Plugin\ShortenUrl\Controllers'-,-'filter=login"/>
        <Pobohet xmlns="http://example/All" url="image/(:segment)" pobohet="get'-,-'post" method="image_view/$1" controller="ImageView" options="namespace=\Plugin\ImageView\Controllers'-,-'"/>
        <Pobohet xmlns="http://example/All" url="url/(:segment)" pobohet="get'-,-'post" method="redirect/$1" controller="ShortenUrl" options="namespace=\Plugin\ShortenUrl\Controllers'-,-'"/>
        <Pobohet xmlns="http://example/All" url="migrations" pobohet="cli" method="index" controller="\Hkm_code\Commands\MigrationsCommand"/>
        <Pobohet xmlns="http://example/All" url="ci(:any)" pobohet="cli" method="index/$1" controller="\Hkm_code\CLI\CommandRunner"/>
        <Pobohet xmlns="http://example/All" url="hkm(:any)" pobohet="cli" method="index/$1" controller="\Hkm_code\CLI\CommandRunnerInstaller"/>
</Pobohets>
