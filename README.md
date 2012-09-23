ez_scaffolding
==============

Description
-------------
Simplified admin generator for the framework php eZ Publish

Goal 
-------------
Welcome to eZ Publish Scaffold, where you can quickly generate your CRUD pages in a module for an eZ Publish instance based on a MySQL database for FREE.

Like an admin generator in Symfony or a Bakery in CakePHP, it will allow you to generate an extension and its module in order to allow you to get Create/Read/Update/Delete access on a MySQL table in less than 5 minutes

The idea is to allow you to keep the control of 100% of the generated code. Once generated you will be able to modify it at your convenience.

+   Generate Create/Read/Update/Delete forms allowing to interact with the given MySQL table
+   Generate validation according to MySQL table parameters
+   Generate pagination
+   Support only one primary key per table
+   Support only varchar/int/text MySQL types for the validation (other types will be considered as simple string)

How to use
-------------
Feel free to use this how you like.

Make sure that the /temp/ folder is writeable.
Then in a browser run index.php. You will see a little documentation about how it's supposed to work. And an exemple to test it.
