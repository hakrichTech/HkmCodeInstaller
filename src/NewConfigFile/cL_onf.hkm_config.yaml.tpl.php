
# Example Environment Configuration file
#
# This file can be used as a starting point for your own
# custom {project name}.yaml files, and contains most of the possible settings
# available in a default install.

# By default, all of the settings are commented out. If you want
# to override the setting, you must un-comment it by removing the '#'
# at the beginning of the line.



# ENVIRONMENT

HKM_ENVIRONMENT: development


# APPLICATION
app:
  baseURL: ${url}
  forceGlobalSecureRequests: false
  ignorePort: true
  filterHome: false
  sessionDriver: Hkm_services\Session\Handler\CookieSessionHandler
  sessionCookieName: hkm_session
  sessionExpiration: 7200
  sessionMatchIP: false
  sessionTimeToUpdate: 300
  sessionRegenerateDestroy: false
  CSPEnabled: false


# DATABASE

database:
  default:
    hostname: localhost
    database: ${dbname}
    username: ${dbuser}
    password: ${dbpass}
    DBDriver: MySQLi
    DBPrefix: 
  tests:
    hostname: localhost
    database: ${dbname}
    username: root
    password: 
    DBDriver: MySQLi
    DBPrefix: 


# CONTENT SECURITY POLICY

contentsecuritypolicy:
  reportOnly: false
  defaultSrc: none
  scriptSrc : self
  styleSrc: self
  imageSrc: self
  base_uri: null
  childSrc: null
  connectSrc: self
  fontSrc: null
  formAction: null
  frameAncestors: null
  frameSrc : null
  mediaSrc: null
  objectSrc: null
  pluginTypes: null
  reportURI : null
  sandbox : false
  upgradeInsecureRequests: false

#--------------------------------------------------------------------
# FILTER
#--------------------------------------------------------------------
filters:
  aliases:
    logout: Hkm_Auth\Filters\Logout
#--------------------------------------------------------------------
# COOKIE
#--------------------------------------------------------------------

cookie:
  prefix: ""
  expires: 500
  path: /
  domain: 
  secure: false
  httponly: false
  samesite: lax
  raw: false


#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

encryption:
  key:
  driver: OpenSSL
  blockSize: 16
  digest: SHA512

#--------------------------------------------------------------------
# HONEYPOT
#--------------------------------------------------------------------

honeypot:
  hidden: true
  label: Fill This Field
  name: honeypot
  template: <label>{label}</label><input type="text" name="{name}" value=""/>
  container: <div style="display:none">{template}</div>

#--------------------------------------------------------------------
# SECURITY
#--------------------------------------------------------------------

security:
  tokenName: csrf_token_name
  headerName: X-CSRF-TOKEN
  cookieName: csrf_cookie_name
  expires: 7200
  regenerate: true
  redirect: true
  samesite: Lax

  
side: CLI

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------

logger:
  threshold: 4


#--------------------------------------------------------------------
# AUTH
#--------------------------------------------------------------------

auth:
  MAX_LOGIN_ATTEMPTS_PER_HOUR: 5
  MAX_PASSWORD_RESET_REQUESTS_PER_DAY: 3
  fields:
    remember: remember
    user_login: username
    user_password: password
  error:
    emptyOrNotString: <strong>Error:</strong> No empty field is allowed!
    wrongPassword: <strong>Error:</strong> Invalid username, email address or incorrect password!
    accountNotVerified: <strong>Error:</strong> Your account is not verified!
    noAccountFound: <strong>Error:</strong> No account found!
    maxRequest: <strong>Error:</strong> You can not reset your password 3 times per day. please try again tomorrow!
    retryAfterHour: <strong>Error:</strong> This account has been blocked, Someone try to access it by attempting too many wrong password. try again after 1 hour!
  passwordResetFields:
    password: password
    hash: hash
    confirmPassword: confirmPassword
    csrf_token: csrf_token
  requestPasswordUser: userIdentification

 





