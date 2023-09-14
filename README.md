## Tugas Backend Development

---

* [API link](ttps://it-perbankan-api.azurewebsites.net)

#### API Method
1. Get Account Balance
2. Get Transactions History
3. Transfer

***

#### GET Request example
* /api.php/balance/{account_id}
* /api.php/transactions/{account_id}

#### POST Request example
* /api.php/transfer
  * POST text body
    * fromAccount=1&toAccount=3&amount=600
  * POST parameter body (JSON)
    ```json
      {
          "fromAccount": 1,
          "toAccount": 2,
          "amount": 500
      }
      ```