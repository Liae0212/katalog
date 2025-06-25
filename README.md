Instrukcja instalacji
1. Pobieramy repo z https://github.com/Liae0212/katalog
2. Rozpakowywujemy projekt w wybranej lokalizacji
3. W pliku .env należy dodajemy odpowiednie dane pozwalające na dostęp do bazy danych
4. W katalogu projektu używamy komendy:
- composer install
5. Następnie:
- bin/console doctrine:migrations:migrate
- bin/console doctrine:fixtures:load
