# Exemplo de Migração SQLite → PostgreSQL

Este exemplo demonstra como migrar um banco SQLite para PostgreSQL utilizando o `pgloader`.

## Comando de migração

```bash
pgloader sqlite:///home/user/adianti/src/app/database/communication.db    postgresql://adianti_user:adianti_pass@localhost:5432/adianti_db

```
## Exemplo de configuração do banco

```php
<?php
return [
    'host'  => 'postgres',
    'port'  => '5432',
    'name'  => 'adianti_db',
    'user'  => 'adianti_user',
    'pass'  => 'adianti_pass',
    'type'  => 'pgsql',
    'prep'  => '1'
];
```