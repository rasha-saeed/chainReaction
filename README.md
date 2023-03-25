Rasha Saeed

1-
Use the assignment.sql file to create the DB

2-
Change the DB config from assignment/config/database.php

3-
Host the project folder on your server, navigate to the project folder, and run the service.
`php -S 127.0.0.1:8081`

4-
Use Postman or any RestFull API client to call the below endpoints.

GET http://localhost:8081/v1/employee
POST http://localhost:8081/v1/employee
PUT http://localhost:8081/v1/employee/1

5-default user is admin@rs.com with pass 12345 add header to the request as below
AUTH_USER=admin@rs.com
AUTH_PASS=12345

