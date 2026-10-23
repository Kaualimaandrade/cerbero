# Cerbero

## OBJETIVOS 
O Cerbero é um sistema web de gerenciamento e administração, desenvolvido para facilitar o controle de usuários, cadastros e solicitações de acesso.
O sistema possui um painel administrativo (Dashboard) com uma interface moderna, organizada, responsiva e fácil de utilizar.
O objetivo principal é centralizar informações, melhorar a segurança, organizar os dados e facilitar as atividades administrativas.
### STACK TECNÓLOGICO
Backend: PHP estruturado em sessões nativas.
Banco de dados: MySQL com PDO para segurança.
Frontend: HTML5, PHP, CSS e Tailwind CSS.
Arquitetura: Separação entre conexão com banco de dados, processamento e interface.
Autenticação: Controle de acesso utilizando sessões PHP.
Hospedagem: Servidor web compatível com PHP e MySQL.

FUNCIONALIDADES DO SISTEMA
#### Regras de negocio (CORE) 
Funcionalidades:
°Exibir a quantidade total de usuários cadastrados.
°Exibir a quantidade de registros cadastrados.Mostrar o status do sistema.
°Exibir solicitações de acesso pendentes.
°Apresentar uma mensagem de boas-vindas ao usuário.
°Identificar o nome e o nível de acesso do usuário conectado.

##### Regras globais
-Mantenha o código limpo e comente apenas lógicas complexas.
-Use sempre o mesmo PDO para conexão e queries no MySQL, utilizando consultas preparadas para evitar SQL Injection.
-Separe os arquivos de forma lógica: um arquivo para conexão com o banco (bd.php), scripts de backend isolados e views em HTML5/PHP.
-Estilize as telas com Tailwind CSS de forma responsiva, priorizando o Mobile First.
-Retorne sempre mensagens de sucesso ou erro de forma clara na interface utilizando TOAST.
-Substitua mensagens nativas do navegador por MODAIS personalizados.
-Valide todos os dados recebidos pelo backend.
-Nunca confie exclusivamente nas validações do frontend.
-Utilize nomes de variáveis e funções claros e organizados.
-Evite duplicação desnecessária de código.

