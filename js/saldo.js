document.addEventListener("DOMContentLoaded", function() {
    function loadBankList() {
        fetch('https://api-rekening.lfourr.com/listBank')
            .then(response => response.json())
            .then(data => {
                console.log("Daftar Bank:", data); // Debug: Tampilkan daftar bank
                const bankSelect = document.getElementById('bank');
                data.forEach(bank => {
                    const option = document.createElement('option');
                    option.value = bank.code;
                    option.textContent = bank.name;
                    bankSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching bank list:', error));
    }

    function loadBankAccountInfo(bankCode, accountNumber) {
        const url = `https://api-rekening.lfourr.com/getBankAccount?bankCode=${bankCode}&accountNumber=${accountNumber}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log("Informasi Rekening:", data); // Debug: Tampilkan informasi rekening
                if (data && data.accountHolder) {
                    document.getElementById('accountHolder').value = data.accountHolder;
                } else {
                    document.getElementById('accountHolder').value = 'Tidak ditemukan';
                }
            })
            .catch(error => console.error('Error fetching bank account info:', error));
    }

    document.getElementById('checkAccount').addEventListener('click', function() {
        const bankCode = document.getElementById('bank').value;
        const accountNumber = document.getElementById('accountNumber').value;
        console.log("Bank Code:", bankCode, "Account Number:", accountNumber); // Debug: Tampilkan input pengguna
        if (bankCode && accountNumber) {
            loadBankAccountInfo(bankCode, accountNumber);
        }
    });

    loadBankList();
});