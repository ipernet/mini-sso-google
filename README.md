Intro
---------------

Used with **Google Apps for Work**, this simple SSO adds a **Google OAuth** layer in front of your services. It can be used as an **extra authentication layer for access** or as an **identification back end** for your services.

It works well with NGinx and its [Auth Request][1] module.

Installation
---------------

- Generate a Google API key to be used by the SSO (see https://developers.google.com/+/web/api/rest/oauth)

- Fill ``config.yml`` as follow:
  - ``base_url``: Root URL where the SSO will be installed.
  - ``cookie.domain``: On which domain/subdomain(s) the SSO cookie will be available.
  - ``google.domains``: A list of domains users accounts must belong to.


- Configure your front webserver to implement client authorization:

``````
server {

        server_name service1.example.org;

        location = /auth {
            internal;
            
            proxy_pass https://mysso.example.org;

            proxy_pass_request_body off;
            proxy_set_header Content-Length "";
            proxy_set_header X-Original-URI $request_uri;
            
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            proxy_set_header X-Broker-Domain $http_host;
        }
        
        error_page 401 = @error401;
        
        location @error401 {
            return 302 https://mysso.example.org/login?from=https://$server_name$request_uri;
        }
        
        # You may not want to do a SSO check on the whole virual host (including static ressources)
        auth_request /auth;
    }
``````

This article may be useful to understand what's going on here: [SSO with Nginx auth_request module][2]

- Install the SSO app on the same domain hierarchy of the services to serve.

The app is based on [Silex][3], if required, please follow its [installation instructions][4] first, before running a ``composer install`` in its directory to install the required dependencies.

**Important**: Make the document root be ``public/`` in order to not expose your ``config.yml`` file.

Usage
------------

Once setup, people accessing your services covered by client authorization will have to sign-in with Google first before beeing granted access to the service. 

The SSO does not replace any additional authentication layers added by your services itself but can serve as an identification service with its **API**.


#Permissions

Custom permissions can be added to **allow or deny** some users to access services.

- Allow all, except:

```yaml
permissions:
	serviceA.example.org:
		deny:
			- bad.user1@example.org
			- bad.user2@example.org
```

- Deny all, except:

```yaml
permissions:
	serviceA.example.org:
		allow:
			- nice.user1@example.org
			- nice.user2@example.org
```

#API

Once the user has gone trough the SSO process, you can retrieve its (Google) identity for the time of the session as follow:

**GET** https://mysso.example.org/

```json
{
  "id": "1157561509350548898",
  "name": "John Smith",
  "domain": "example.org",
  "email": "user@example.org"
}
```

Or using ``jsonp``:

**GET** https://mysso.example.org/?jsonp_callback=ssoCall

```json
ssoCall ({
  "id": "1157561509350548898",
  "name": "John Smith",
  "domain": "example.org",
  "email": "user@example.org"
})
```

**Note:** Using server side code, you must pass the SSO cookie in the request header of the call.

[1]: http://nginx.org/en/docs/http/ngx_http_auth_request_module.html

[2]: http://wiki.shopware.com/SSO-with-Nginx-auth_request-module_detail_1811.html

[3]: http://silex.sensiolabs.org/

[4]: http://silex.sensiolabs.org/doc/web_servers.html