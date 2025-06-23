#!/bin/bash
CONTAINER_NAME=dss-analisis-de-riesgo-main-db-1
DB_USER=usuario
DB_PASSWORD=clave
DB_NAME=dss_db
SQL_DIR=sql_implementados

for file in "$SQL_DIR"/*.sql; do
  echo "Importando $file..."
  docker exec -i $CONTAINER_NAME mysql -u $DB_USER -p$DB_PASSWORD $DB_NAME < "$file"
done
echo "✅ Importación de todas las bases de datos finalizada."
