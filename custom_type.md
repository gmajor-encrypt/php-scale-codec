## Substrate Custom Type format

There custom type are same with polkadot.js type.json


### String

```json

{
  "typeName": "inheritTypeName"
}
```

Example

```json
{
  "address": "H256"
}

```

### Struct

```json

{
  "typeName": {
    "field1": "inheritTypeName",
    "field2": "inheritTypeName2"
  }
}
```


Example
```json
{
    "BalanceLock<Balance>": {
      "id": "LockIdentifier"
    }
}

```


### Enum


```json

{
  "typeName": {
    "_enum": {
      "field1": "inheritTypeName",
      "field2": "Null"
    }
  }
}
```

Example
```json
{
  "Owner": {
    "_enum": {
      "None": "Null",
      "Address": "AccountId"
    }
  }
}

```

### Tuple

```json

{
  "typeName":"(field1,field2....)"
}
```

Example
```json
{
  "VotingDirectVote": "(ReferendumIndex, AccountVote)"
}

```


### Set

```json

{
  "typeName":{
    "_set": {
      "field1": 1,
      "field2": 2,
      "field3": 4,
      "field4": 8,
      "field5": 16
    }
  }
}
```

Example
```json
{
  "WithdrawReasons": {
    "_set": {
      "TransactionPayment": 1,
      "Transfer": 2,
      "Reserve": 4,
      "Fee": 8,
      "Tip": 16
    }
  }
}

```

### Result

```json

{
  "typeName":"Results<type1, type2>"
}
```

Example
```json
{
  "ResultType": "Result<u8, bool>"
}

```