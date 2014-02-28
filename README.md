Djokka Framework
================

Djokka Framework is PHP Framework using HMVC (Hierarchical Model-View-Controller) architecture and build-in IDE (since v1.0.3). Djokka Framework is has very simple HMVC structure.

HMVC Structure
==============

```
+--modules
|  |
|  +--blog (Module)
|  |  |
|  |  +--Blog.php (Controller)
|  |  |
|  |  +--views
|  |  |  |
|  |  |  +--index.php
|  |  |  +--form.php
|  |  |
|  |  +--models
|  |  |  |
|  |  |  +--SigninForm.php
|  |  |
|  |  +--modules
|  |     |
|  |     +--comment (Sub-module)
|  |        |
|  |        +--Comment.php (Controller)
|  |        |
|  |        +--views
|  |           |
|  |           +--read.php
|  |
|  +--member (Module)
|     |
|     +--Member.php (Controller)
|     |
|     +--views
|     |  |
|     |  +--index.php
|     |  +--form.php
|     |
|     +--models
|        |
|        +--SignupForm.php
|
+--models (Global model)
   |
   +--Member.php
   +--Blog.php
   +--Gallery.php
   +--Agenda.php
```

This is an example hierarchical module. There is 2 module in root, blog and member. A module is has controller and can has views and models.
If you don't like to place your model inside module, you can place it to 'models' as global model. Every module can access the global model.

Look into module 'blog', you'll see the sub-module with name 'comment'. Yeah, that's the HMVC means. The module must not be has view or model,
but must be has a controller. The controller of module is same with the module name with capitalized format.

========

Djokka Framework is started develop at February 17th 2013

By [Ahmad Jawahir](http://twitter.com/ahmjw) <rawndummy@gmail.com>