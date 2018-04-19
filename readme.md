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


- restApi

    -GET - url: http://nabor.alistra.cloud/api/messages
    - hlavička
            
            - Authorization: apiToken
            
    -POST - url: http://nabor.alistra.cloud/api/messages
    - hlavička
              
              - Authorization: apiToken
              - user_id: 1
              - text: nejaky text
              
    -DELETE - url: http://nabor.alistra.cloud/api/messages/1
    - hlavička
              
              - Authorization: apiToken
