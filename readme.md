Nette Chat Project
=================

**Funkčnost**

- Uživatelé
    - Uživatel musí být registrován, požadované informace: Jméno, E-mail, Heslo.
    - Neuvidí admin sekci
- Administrátor
    - uvidí přehled registrovaných uživatelů.
    - může editovat/smazat uživatele, nebo mu nastavit admin roli
    - má možnost procházet zprávy
    - může přidat přístupový token (REST APi)
- Chat
    - Každý registrovaný uživatel může odeslat jednoduchý text
    - Chatovací okno
        - chatovací okno je jedno společné pro všechny uživatelé
        - každou sekundu obnovovat (Nette ajax)
        - zobrazovat posledních 10 zpráv
        - u zprávy zobrazovat jméno a čas
- REST APi
    - ověření tokenu 
    - možnost nahrát zprávu do chatu
    - možnost vrátit X zpráv
    - možnost smazat určitou zprávu


restApi
-GET - url: http://nabor.alistra.cloud/api/rooms/

hlavička

    - Authorization: apiToken

= Vypíše seznam místností i s moderatory
-POST - url:  http://nabor.alistra.cloud/api/rooms/

hlavička

    - Authorization: apiToken
    - name: text nazev mistnosti
    - description: text popis mistnosti

Když se přidá 
    - moderator: id moderatoru, oddělovač čárka, tak se vloží k vytvořené místnosti

= Založí novou skupinu, případně s moderátory
-PUT - url:  http://nabor.alistra.cloud/api/rooms/1  

hlavička

    - Authorization: apiToken
    - id: id skupiny
    - name: text nazev mistnosti
    - description: text popis mistnosti
Když se přidá 
    - moderator: id moderatoru, oddělovač čárka, tak se vloží k vytvořené místnosti

= Aktualizuje skupinu, případně moderátory (smaže všechny a nově přidá)
-DELETE - url: http://nabor.alistra.cloud/api/rooms/1

hlavička

    - Authorization: apiToken
