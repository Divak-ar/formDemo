

---

## 🏗 Project Layout & Docker Setup

### 1. Create workspace folder and initialize

Open PowerShell:

```powershell
mkdir C:\magento2-vscode-docker
cd C:\magento2-vscode-docker
```

### 2. Create `docker-compose.yml` (Magento + MySQL + Nginx + MailHog)

Create file `docker-compose.yml` with:

```yaml
version: "3.8"

services:
  db:
    image: mysql:8.0
    container_name: magento_db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_DATABASE: magento
      MYSQL_USER: magento
      MYSQL_PASSWORD: magento
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "3307:3306"

  php:
    image: php:8.1-fpm
    container_name: magento_php
    volumes:
      - ./src:/var/www/html
      - ./php.ini:/usr/local/etc/php/php.ini
    working_dir: /var/www/html
    depends_on:
      - db
    environment:
      COMPOSER_ALLOW_SUPERUSER: "1"
    command: ["php-fpm"]

  web:
    image: nginx:stable
    container_name: magento_nginx
    depends_on:
      - php
    volumes:
      - ./src:/var/www/html:delegated
      - ./nginx.conf:/etc/nginx/conf.d/default.conf:ro
    ports:
      - "8080:80"

  composer:
    image: composer:2
    container_name: magento_composer
    volumes:
      - ./src:/app
    working_dir: /app
    entrypoint: ["composer"]

  mailhog:
    image: mailhog/mailhog
    container_name: magento_mailhog
    ports:
      - "1025:1025" # SMTP
      - "8025:8025" # Web UI

volumes:
  db_data:
```

### 3. Supporting config files

#### `nginx.conf`

```nginx
server {
    listen 80;
    server_name localhost;
    set $MAGE_ROOT /var/www/html;
    root $MAGE_ROOT/pub;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param MAGE_MODE developer;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires max;
        try_files $uri $uri/ /index.php?$args;
    }
}
```

#### `php.ini` (minimal)

```ini
display_errors=1
memory_limit=2G
upload_max_filesize=64M
post_max_size=64M
date.timezone=UTC
```

---

## ⚙️ Step-by-step Setup in VSCode + Docker

### 1. Open folder in VSCode

Open `C:\magento2-vscode-docker` in VSCode.

### 2. Create Dev Container (optional but helps)

File: `.devcontainer/devcontainer.json`

```json
{
  "name": "Magento2 Dev",
  "dockerComposeFile": "../docker-compose.yml",
  "service": "php",
  "workspaceFolder": "/var/www/html",
  "extensions": [
    "bmewburn.vscode-intelephense-client",
    "ms-azuretools.vscode-docker"
  ],
  "settings": {
    "php.validate.executablePath": "/usr/local/bin/php"
  },
  "shutdownAction": "stopCompose"
}
```

Then use the Command Palette: **"Remote-Containers: Reopen in Container"**.
This opens a shell inside the `php` container at `/var/www/html`.

### 3. Fetch Magento via Composer inside container

In VSCode terminal (inside container):

```bash
# create Magento project skeleton
composer create-project --repository=https://repo.magento.com/ magento/project-community-edition=2.4.6 src --no-interaction
```

You will need Magento authentication keys. If you don't have them, you can skip validation but for a full install register at marketplace.magento.com and get public/private.
*(For learning you can also use a bare copy or skip composer and assume `src/` contains Magento code)*

### 4. Install dependencies and set permissions

```bash
cd src
bin/magento setup:install \
--base-url=http://localhost:8080/ \
--db-host=db \
--db-name=magento \
--db-user=magento \
--db-password=magento \
--admin-firstname=Admin \
--admin-lastname=User \
--admin-email=admin@example.com \
--admin-user=admin \
--admin-password=Admin123! \
--backend-frontname=admin \
--language=en_US \
--currency=USD \
--timezone=UTC \
--use-rewrites=1
```

*(If DB not ready, wait a few seconds for MySQL container then retry.)*

### 5. Access

* Frontend: [http://localhost:8080](http://localhost:8080)
* Admin: [http://localhost:8080/admin](http://localhost:8080/admin)  (use admin/Admin123!)

MailHog UI: [http://localhost:8025](http://localhost:8025) to view emails.

---

## 🧱 Module: `EMP123_FormDemo`

### Folder structure (create under `src/app/code/EMP123/FormDemo`):

```
app/code/EMP123/FormDemo/
├── etc/
│   ├── module.xml
│   ├── frontend/
│   │   └── routes.xml
│   ├── adminhtml/
│   │   └── routes.xml
│   ├── acl.xml
│   ├── menu.xml
│   └── email_templates.xml
├── registration.php
├── Model/
│   ├── FormData.php
│   └── ResourceModel/
│       ├── FormData.php
│       └── FormData/
│           └── Collection.php
├── view/
│   ├── frontend/
│   │   ├── layout/
│   │   │   └── formdemo_index_index.xml
│   │   ├── templates/
│   │   │   └── form.phtml
│   │   └── email/
│   │       └── form_submission.html
│   └── adminhtml/
│       └── ui_component/
│           └── formdemo_grid.xml
├── Controller/
│   ├── Index/
│   │   ├── Index.php
│   │   └── Post.php
│   └── Adminhtml/
│       └── Grid/
│           ├── Index.php
│           ├── Edit.php
│           ├── Save.php
│           ├── NewAction.php
│           └── MassDelete.php
└── etc/db_schema.xml
```

---

### Key file contents

#### `registration.php`

```php
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'EMP123_FormDemo',
    __DIR__
);
```

#### `etc/module.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
  <module name="EMP123_FormDemo" setup_version="1.0.0"/>
</config>
```

#### `etc/db_schema.xml`

```xml
<?xml version="1.0"?>
<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:noNamespaceSchemaLocation="urn:magento:framework/Setup/Declaration/Schema/etc/schema.xsd">
  <table name="form_data" resource="default" engine="innodb" comment="Form Demo Data">
    <column xsi:type="int" name="entity_id" padding="10" nullable="false" identity="true" unsigned="true" primary="true" comment="Entity ID"/>
    <column xsi:type="varchar" name="first_name" nullable="false" length="255" comment="First Name"/>
    <column xsi:type="varchar" name="last_name" nullable="false" length="255" comment="Last Name"/>
    <column xsi:type="varchar" name="email" nullable="false" length="255" comment="Email"/>
    <column xsi:type="date" name="dob" nullable="false" comment="Date of Birth"/>
    <column xsi:type="varchar" name="gender" nullable="false" length="50" comment="Gender"/>
    <column xsi:type="varchar" name="telephone" nullable="false" length="50" comment="Telephone"/>
    <column xsi:type="timestamp" name="created_at" nullable="false" default="CURRENT_TIMESTAMP" on_update="false" comment="Created At"/>
    <constraint xsi:type="unique" referenceId="FORMDATA_EMAIL_UNIQUE">
      <column name="email"/>
    </constraint>
  </table>
</schema>
```

#### `etc/frontend/routes.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="urn:magento:framework/App/etc/routes.xsd">
  <router id="standard">
    <route id="formdemo" frontName="formdemo">
      <module name="EMP123_FormDemo"/>
    </route>
  </router>
</config>
```

#### `etc/adminhtml/routes.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="urn:magento:framework/App/etc/routes.xsd">
  <router id="admin">
    <route id="formdemo" frontName="formdemo">
      <module name="EMP123_FormDemo"/>
    </route>
  </router>
</config>
```

#### `etc/acl.xml`

```xml
<?xml version="1.0"?>
<acl xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="urn:magento:framework/Acl/etc/acl.xsd">
  <resources>
    <resource id="Magento_Backend::admin">
      <resource id="EMP123_FormDemo::main" title="FormDemo" sortOrder="10">
        <resource id="EMP123_FormDemo::demo_grid" title="Demo Grid" sortOrder="10"/>
      </resource>
    </resource>
  </resources>
</acl>
```

#### `etc/menu.xml`

```xml
<?xml version="1.0"?>
<menu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="urn:magento:framework/Menu/etc/menu.xsd">
  <add id="EMP123_FormDemo::main" title="FormDemo" module="EMP123_FormDemo" sortOrder="100" parent="Magento_Backend::content" resource="EMP123_FormDemo::main"/>
  <add id="EMP123_FormDemo::demo_grid" title="Demo Grid" module="EMP123_FormDemo" sortOrder="10" parent="EMP123_FormDemo::main" action="formdemo/grid/index" resource="EMP123_FormDemo::demo_grid"/>
</menu>
```

#### `etc/email_templates.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Email:etc/email_templates.xsd">
  <template id="formdemo_email_template" label="FormDemo Submission Email" file="form_submission.html" type="html" module="EMP123_FormDemo" area="frontend"/>
</config>
```

---

### Models

#### `Model/FormData.php`

```php
<?php
namespace EMP123\FormDemo\Model;

use Magento\Framework\Model\AbstractModel;

class FormData extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\EMP123\FormDemo\Model\ResourceModel\FormData::class);
    }
}
```

#### `Model/ResourceModel/FormData.php`

```php
<?php
namespace EMP123\FormDemo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FormData extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('form_data', 'entity_id');
    }
}
```

#### `Model/ResourceModel/FormData/Collection.php`

```php
<?php
namespace EMP123\FormDemo\Model\ResourceModel\FormData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \EMP123\FormDemo\Model\FormData::class,
            \EMP123\FormDemo\Model\ResourceModel\FormData::class
        );
    }
}
```

---

### Frontend: Form display & submission

#### `Controller/Index/Index.php`

```php
<?php
namespace EMP123\FormDemo\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
```

#### `Controller/Index/Post.php`

```php
<?php
namespace EMP123\FormDemo\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use EMP123\FormDemo\Model\FormDataFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

class Post extends Action
{
    protected $formFactory;
    protected $messageManager;
    protected $transportBuilder;
    protected $storeManager;
    protected $scopeConfig;
    protected $resultRedirectFactory;
    protected $formKeyValidator;

    public function __construct(
        Context $context,
        FormDataFactory $formFactory,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        RedirectFactory $resultRedirectFactory,
        Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->formFactory = $formFactory;
        $this->messageManager = $messageManager;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getPostValue();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage('Invalid form key.');
            return $resultRedirect->setPath('*/*/');
        }

        foreach (['first_name','last_name','email','dob','gender','telephone'] as $field) {
            if (empty($post[$field])) {
                $this->messageManager->addErrorMessage(ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                return $resultRedirect->setPath('*/*/');
            }
        }

        if (!\Zend_Validate::is($post['email'], 'EmailAddress')) {
            $this->messageManager->addErrorMessage('Invalid email.');
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $form = $this->formFactory->create();
            $form->setData([
                'first_name' => $post['first_name'],
                'last_name' => $post['last_name'],
                'email' => $post['email'],
                'dob' => $post['dob'],
                'gender' => $post['gender'],
                'telephone' => $post['telephone'],
            ]);
            $form->save();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('formdemo_email_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ])
                ->setTemplateVars(['data' => $post])
                ->setFrom('general')
                ->addTo($post['email'])
                ->getTransport();
            $transport->sendMessage();

            $this->messageManager->addSuccessMessage('Submission successful.');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Submission failed: ' . $e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
```

---

### Frontend layout & template

#### `view/frontend/layout/formdemo_index_index.xml`

```xml
<?xml version="1.0"?>
<page layout="1column" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
  <body>
    <referenceContainer name="content">
      <block class="Magento\Framework\View\Element\Template" name="formdemo.form" template="EMP123_FormDemo::form.phtml"/>
    </referenceContainer>
  </body>
</page>
```

#### `view/frontend/templates/form.phtml`

```php
<?php /** @var $block \Magento\Framework\View\Element\Template */ ?>
<form action="<?= $block->getUrl('formdemo/index/post') ?>" method="post">
    <?= $block->getBlockHtml('formkey'); ?>
    <div>
        <label>First Name</label>
        <input name="first_name" required/>
    </div>
    <div>
        <label>Last Name</label>
        <input name="last_name" required/>
    </div>
    <div>
        <label>Email</label>
        <input type="email" name="email" required/>
    </div>
    <div>
        <label>Date of Birth</label>
        <input type="date" name="dob" required/>
    </div>
    <div>
        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
    </div>
    <div>
        <label>Telephone</label>
        <input name="telephone" required/>
    </div>
    <div>
        <button type="submit">Submit</button>
    </div>
</form>
```

---

### Email template

#### `view/frontend/email/form_submission.html`

```html
<p>Hi {{var data.first_name}},</p>
<p>Thank you for submitting the form. Here is what we received:</p>
<ul>
  <li>First Name: {{var data.first_name}}</li>
  <li>Last Name: {{var data.last_name}}</li>
  <li>Email: {{var data.email}}</li>
  <li>Date of Birth: {{var data.dob}}</li>
  <li>Gender: {{var data.gender}}</li>
  <li>Telephone: {{var data.telephone}}</li>
</ul>
<p>Regards,<br/>FormDemo Team</p>
```

---

### Admin Grid UI Component (simplified skeleton)

#### `view/adminhtml/ui_component/formdemo_grid.xml`

```xml
<?xml version="1.0"?>
<listing xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">
  <argument name="data" xsi:type="array">
    <item name="js_config" xsi:type="array">
      <item name="provider" xsi:type="string">formdemo_grid.formdemo_grid_data_source</item>
      <item name="deps" xsi:type="string">formdemo_grid.formdemo_grid_data_source</item>
    </item>
    <item name="spinner" xsi:type="string">columns</item>
  </argument>
  <dataSource name="formdemo_grid_data_source">
    <argument name="dataProvider" xsi:type="configurableObject">
      <argument name="class" xsi:type="string">EMP123\FormDemo\Ui\DataProvider\FormDataProvider</argument>
      <argument name="name" xsi:type="string">formdemo_grid_data_source</argument>
      <argument name="primaryFieldName" xsi:type="string">entity_id</argument>
      <argument name="requestFieldName" xsi:type="string">entity_id</argument>
    </argument>
  </dataSource>
  <columns name="columns">
    <column name="first_name">
      <settings>
        <label translate="true">First Name</label>
      </settings>
    </column>
    <column name="last_name">
      <settings>
        <label translate="true">Last Name</label>
      </settings>
    </column>
    <column name="email">
      <settings>
        <label translate="true">Email</label>
      </settings>
    </column>
    <column name="dob">
      <settings>
        <label translate="true">DOB</label>
      </settings>
    </column>
    <column name="gender">
      <settings>
        <label translate="true">Gender</label>
      </settings>
    </column>
    <column name="telephone">
      <settings>
        <label translate="true">Telephone</label>
      </settings>
    </column>
    <actionsColumn name="actions">
      <settings>
        <indexField>entity_id</indexField>
      </settings>
    </actionsColumn>
  </columns>
</listing>
```

*(You’d also need corresponding UI data provider and admin controllers for edit/save/massDelete — follow standard Magento 2 grid patterns; if you want I can generate those next in a canvas)*

---

## 🔧 Activation & Commands (inside container)

After placing module code:

```bash
cd src
php bin/magento module:enable EMP123_FormDemo
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

---

## 📨 Email Testing

Magento will send email via default transport (you can configure SMTP to MailHog by setting `mail.smtp` in `app/etc/env.php` or override transport to point to `mailhog:1025`).
MailHog UI: [http://localhost:8025](http://localhost:8025)

Example minimal override via env.php (patch or plugin) to use SMTP host `mailhog` port `1025`.


**What to do now in VSCode:**

1. Paste all files (create missing controllers for admin CRUD if needed).
2. Run the activation commands above inside the `php` container.
3. Visit frontend: `http://localhost:8080/formdemo` to see the form.
4. Submit and verify in MailHog.
5. Go to Admin and ensure menu appears; assign permission to a role for `FormDemo > Demo Grid`.


