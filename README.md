# mysql-watcher

mysql-watcher is a simple PHP script that allows you to watch mysql queries' result just like "tail -f" gonna do with a text file.
Eg, you can watch the result of "select * from mytable order by id desc limit 10". Then if new rows are added to "mytable" you could see the result instantly.

You have to configure mysql-watcher.php file once with your username, pass, mysql host (usually "localhost") and your database. Then you can watch any query just doing: php mysql-watcher.php <query>

# Example
php mysql-watcher.php "select * from mytable"
