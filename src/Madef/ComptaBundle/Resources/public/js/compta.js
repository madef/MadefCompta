$(function() {
    var lastChanged = ['valueTaxExclude', 'valueTaxInclude'];
    $('#valueTaxExclude, #valueTaxInclude, #taxValue, #taxRate').change(function() {
        if (lastChanged.indexOf($(this).attr('id')) == -1) {
            if (lastChanged.length >= 2) {
                lastChanged.shift();
            }
            lastChanged.push($(this).attr('id'));
        }
        if (lastChanged.length < 2) {
            return;
        }

        if (lastChanged[0] == 'valueTaxExclude' && lastChanged[1] == 'valueTaxInclude' || lastChanged[1] == 'valueTaxExclude' && lastChanged[0] == 'valueTaxInclude') {
            $('#taxValue').val($('#valueTaxInclude').val().toFloat() - $('#valueTaxExclude').val().toFloat());
            $('#taxRate').val(
                    ($('#valueTaxInclude').val().toFloat() - $('#valueTaxExclude').val().toFloat()) / $('#valueTaxExclude').val().toFloat() * 100
                    );
        } else if (lastChanged[0] == 'valueTaxExclude' && lastChanged[1] == 'taxValue' || lastChanged[1] == 'valueTaxExclude' && lastChanged[0] == 'taxValue') {
            $('#valueTaxInclude').val($('#taxValue').val().toFloat() + $('#valueTaxExclude').val().toFloat());
            $('#taxRate').val(
                    $('#taxValue').val().toFloat() / $('#valueTaxExclude').val().toFloat() * 100
                    );
        } else if (lastChanged[0] == 'valueTaxInclude' && lastChanged[1] == 'taxValue' || lastChanged[1] == 'valueTaxInclude' && lastChanged[0] == 'taxValue') {
            $('#valueTaxExclude').val($('#valueTaxInclude').val().toFloat() - $('#taxValue').val().toFloat());
            $('#taxRate').val(
                    $('#taxValue').val().toFloat() / $('#valueTaxExclude').val().toFloat() * 100
                    );
        } else if (lastChanged[0] == 'valueTaxInclude' && lastChanged[1] == 'taxRate' || lastChanged[1] == 'valueTaxInclude' && lastChanged[0] == 'taxRate') {
            $('#valueTaxExclude').val(
                    $('#valueTaxInclude').val().toFloat() / (100 + $('#taxRate').val().toFloat()) * 100
                    );
            $('#taxValue').val(
                    $('#taxRate').val().toFloat() * $('#valueTaxExclude').val().toFloat() / 100
                    );
        } else if (lastChanged[0] == 'valueTaxExclude' && lastChanged[1] == 'taxRate' || lastChanged[1] == 'valueTaxExclude' && lastChanged[0] == 'taxRate') {
            $('#valueTaxInclude').val(
                    $('#valueTaxExclude').val().toFloat() * (100 + $('#taxRate').val().toFloat()) / 100
                    );
            $('#taxValue').val(
                    $('#taxRate').val().toFloat() * $('#valueTaxExclude').val().toFloat() / 100
                    );
        } else {
            $('#valueTaxExclude').val(
                    $('#taxValue').val().toFloat() * 100 / $('#taxRate').val().toFloat()
                    );
            $('#valueTaxInclude').val($('#taxValue').val().toFloat() + $('#valueTaxExclude').val().toFloat());
        }

        $('#valueTaxInclude').val($('#valueTaxInclude').val().toFloat().round());
        $('#valueTaxExclude').val($('#valueTaxExclude').val().toFloat().round());
        $('#taxRate').val($('#taxRate').val().toFloat().round());
        $('#taxValue').val($('#taxValue').val().toFloat().round());
    });


    $('#invoiceSearch').keyup(function(e) {
        if (!$(this).val().length) {
            $('#invoiceId').val('');
        }
    });
    $('#invoiceSearch').typeahead({
        source: function(query, callback) {
            $.ajax({
                url: $('#invoiceSearch').attr('data-url'),
                data: {
                    query: query,
                    amountTaxInclude: $('#valueTaxInclude').val()
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    callback(res);
                }
            });
        },
        matcher: function() {
            return true;
        }, // Dont check results
        updater: function(item) {
            $('#invoiceId').val((item.replace(/^(\d+)\s-.*$/, '$1')));
            return item;
        }
    });
});


Number.prototype.round = function(n) {
    if (typeof(n) == 'undefined') {
        n = 2
    }
    var decade = Math.pow(10, n);
    return Math.round(this * decade) / decade;
}

String.prototype.toFloat = function() {
    var value = parseFloat(this.replace(',', '.'));
    if (isNaN(value)) {
        return 0;
    }
    return value;
}