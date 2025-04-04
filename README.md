
# API de Cotações de Frete

Este é um projeto de API que permite consultar cotações de frete utilizando dados fictícios. Ele é containerizado com Docker e foi desenvolvido seguindo boas práticas de programação, incluindo TDD e validação de dados de entrada. A API permite consultar cotações de frete de uma transportadora fictícia e armazenar os resultados para consultas posteriores sobre métricas de cotações.

## Objetivo

Desenvolver uma API Rest que oferece duas rotas principais para consulta de cotações e métricas de frete:

1. **quotes**: Realiza uma cotação de frete fictícia utilizando a API da Frete Rápido, armazena as cotações no banco de dados e retorna as informações de preço e transportadora.
2. **metrics**: Retorna métricas sobre as cotações armazenadas no banco de dados, como o preço médio, total por transportadora, e os fretes mais baratos e caros.

## Funcionalidades

### Rota 1: [POST] `/quotes`

- **Objetivo**: Receber dados de entrada em JSON para realizar uma cotação de frete fictícia com a API da Frete Rápido.
- **Entrada**: Recebe um JSON com as informações do destinatário, volumes e outras informações necessárias para realizar a cotação.
  
  Exemplo de entrada:

  ```json
   {
    "origin_cep": "01001000",
    "destination_cep": "29161376",
    "shipper": {
        "registered_number": "25438296000158",
        "token": "1d52a9b6b78cf07b08586152459a5c90",
        "platform_code": "5AKVkHqCn"
    },
    "volumes": [
        {
        "amount": 1,
        "width": 20,
        "height": 20,
        "length": 20,
        "unitary_weight": 1,
        "unitary_price": 50.00,
        "category": "70"
        }
    ]
    }

  ```

- **Processo**: Com os dados de entrada, a API complementa os dados obrigatórios para consumir a API da Frete Rápido e realizar a cotação. Os resultados das cotações são gravados no banco de dados para serem consumidos posteriormente.

- **Retorno Esperado**:

  ```json
  {
     "carrier": [
        {
           "name": "EXPRESSO FR",
           "service": "Rodoviário",
           "deadline": "3",
           "price": 17
        },
        {
           "name": "Correios",
           "service": "SEDEX",
           "deadline": 1,
           "price": 20.99
        }
     ]
  }
  ```

### Rota 2: [GET] `/metrics?last_quotes={?}`

- **Objetivo**: Consultar métricas das cotações armazenadas no banco de dados, permitindo receber um parâmetro opcional `last_quotes` para indicar a quantidade de cotações (ordem decrescente).
- **Processo**: A API consulta as cotações armazenadas e retorna as seguintes métricas:
  - Quantidade de resultados por transportadora.
  - Total de "preco_frete" por transportadora.
  - Média de "preco_frete" por transportadora.
  - O frete mais barato geral.
  - O frete mais caro geral.

- **Exemplo de Retorno**:

  ```json
  {
     "code": 200,
     "message": "Métricas recuperadas com sucesso.",
     "per_carrier": {
        "EXPRESSO FR": {
           "count": 10,
           "total_price": 170,
           "avg_price": 17
        },
        "Correios": {
           "count": 8,
           "total_price": 167.92,
           "avg_price": 20.99
        }
     },
     "min_price": 17,
     "max_price": 20.99
  }
  ```

## Como Rodar

### Requisitos

- Docker e Docker Compose instalados.

### Passos para execução

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/seu-repositorio.git
   cd seu-repositorio
   ```

2. Crie e inicie os containers com Docker Compose:
   ```bash
   docker-compose up --build
   ```

3. Acesse a API via `localhost` ou o endereço configurado no seu Docker.

### Testes

Para rodar os testes, você pode usar o seguinte comando:

```bash
docker exec -it seu_container_php vendor/bin/phpunit --coverage-html ./build/coverage
```

## Tecnologias Utilizadas

- PHP
- Docker
- PHPUnit
- MySQL (ou outro banco de dados relacional)
- API da Frete Rápido
