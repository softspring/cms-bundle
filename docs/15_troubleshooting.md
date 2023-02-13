# Troubleshooting

## Mysql memory allocation error

In some situations, Doctrine can dispatch a "Doctrine\DBAL\Exception\DriverException" exception with an "out of sort memory" message.

> SQLSTATE[HY001]: Memory allocation error: 1038 Out of sort memory, consider increasing server sort buffer size

This is a common error if database instance has a low value for [sort_buffer_size](https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_sort_buffer_size) variable.

The solution is easy: increase this value, for example to 512k.

