Validation.add('validate-for-discount', 'Please enter a correct data.', function (regSearch) {
    return /^(-?\d+(\.\d+)?%?)?$/.test(regSearch);
});
