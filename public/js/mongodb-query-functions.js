function ISODate(dateStr) {
    return {
        $date: {
            $numberLong: new Date(dateStr).getTime().toString()
        }
    }
}

function ObjectId(str) {
    return {
        $oid: str
    }
}

function NumberLong(number) {
    return number
}

function NumberInt(number) {
    return number
}

function NumberDecimal(str) {
    return {
        $numberDecimal: str
    }
}
