# Stock modules:

- Accounts
- Contacts
- Leads
- Invoices
- Quotes
- Cases
- Emails
- Inquiries

# Stock Field Types:

![For Now only user cannot create fields Types]

| Field Name    | database type | frontend type | vue Component |
| ------------- | ------------- | ------------- | ------------- |
| textField     | varchar(255)  | input=text    | no            |
| longText      | longtext      | textarea      | no            |
| date          | date          | input=date    | no (to do)    |
| dateTime      | TimeStamp     | input=date    | no (to do)    |
| switcher      | varchar(255)  | Switcher      | yes           |
| dropDownField | varchar(255)  | DropdownField | yes           |
| checkbox      | boolean       | CheckBox      | yes           |
| email         | varchar(255)  | input=email   | no            |
| number        | int           | input=number  | no (to do)    |

## Fields metadata

| name    | database type |
| ------- | ------------- |
| key     | varchar(255)  |
| Label   | varchar(255)  |
| Type    | varchar(255)  |
| db_type | ![no stored]  |

## Fields Database Structure

### Stock Fields

Stock fields are normal database columns in the module SQL table. These fields are not to be edited, deleted or updated.
The labels of these fields are stored in `/lang/[lang]/modules/`

### Custom Fields

Everytime a new custom field is created we trigger this work flow:

- Check if table ´[module]\_custom_fields´ exists
- if exists create an new column ´[field_name]´
- if table does not exist create new table with the naming above and the following columns: ´id´ (which will be then the record id and used as a joiner for the module table) + ´[field_name]´
