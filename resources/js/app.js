import './bootstrap';
import './member-location';

// LIVE CLOCK
const clock = document.querySelector('[data-live-clock]');

if (clock) {
    const formatter = new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        dateStyle: 'long',
        timeStyle: 'short',
    });

    function updateClock() {
        const now = new Date();

        clock.textContent = formatter.format(now).replace(' at ', ' | ');
        clock.dateTime = now.toISOString();
    }

    updateClock();
    setInterval(updateClock, 1000);
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});

const transactionMember = document.querySelector('[data-transaction-member]');
if (transactionMember) {
    const updateSummary = () => {
        const option = transactionMember.selectedOptions[0];
        document.querySelector('[data-transaction-summary]').textContent = option.dataset.fee
            ? `Total membership fee: ₱${Number(option.dataset.fee).toLocaleString('en-PH', { minimumFractionDigits: 2 })} · Validity: ${option.dataset.duration} months. The fee is saved when you create this transaction.`
            : 'Choose a member to preview the fee and validity period.';
    };
    transactionMember.addEventListener('change', () => {
        document.querySelector('#type').value = transactionMember.selectedOptions[0].dataset.type || 'application';
        updateSummary();
    });
    updateSummary();
}

const paymentTransaction = document.querySelector('[data-payment-transaction]');
if (paymentTransaction) {
    const updateBalance = () => {
        const balance = paymentTransaction.selectedOptions[0].dataset.balance;
        const amount = document.querySelector('#amount');
        amount.max = balance || '99999999.99';
        document.querySelector('[data-payment-summary]').textContent = balance
            ? `Outstanding balance: ₱${Number(balance).toLocaleString('en-PH', { minimumFractionDigits: 2 })}. You may record a full or partial payment.`
            : 'Choose a transaction to see its outstanding balance.';
    };
    paymentTransaction.addEventListener('change', updateBalance);
    updateBalance();
}

const paymentMethod = document.querySelector('#payment_method');
if (paymentMethod) {
    const updateReference = () => {
        document.querySelector('#reference_number').required = paymentMethod.value !== 'cash';
    };
    paymentMethod.addEventListener('change', updateReference);
    updateReference();
}
