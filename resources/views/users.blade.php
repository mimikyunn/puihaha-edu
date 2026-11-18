<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <!-- <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f1f1; color: #202020; font-family: Arial, sans-serif; font-size: 13px; }
        button, input, select { font: inherit; }
        button { cursor: pointer; }
        button:disabled { cursor: default; opacity: .45; }
        button:focus-visible, input:focus-visible, select:focus-visible { outline: 2px solid #505050; outline-offset: 3px; }
        .users-panel { max-width: 1200px; min-height: calc(100vh - 112px); margin: 56px auto; padding: 26px; background: #fff; border-radius: 12px; }
        .toolbar, .actions, .dialog-heading, .dialog-actions, .table-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .toolbar { margin-bottom: 28px; }
        h1 { margin: 0; font-size: 19px; font-weight: 600; }
        .actions { justify-content: flex-end; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 36px; padding: 9px 15px; border: 1px solid transparent; border-radius: 7px; font-size: 12px; font-weight: 500; }
        .button-dark { background: #191919; color: #fff; }
        .button-dark:hover { background: #363636; }
        .button-danger { background: #ff4b54; color: #fff; }
        .button-danger:not(:disabled):hover { background: #e63842; }
        .button-light { background: #fff; border-color: #dedede; color: #333; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; white-space: nowrap; }
        th { padding: 12px 10px; color: #171717; font-size: 12px; font-weight: 600; }
        td { padding: 14px 10px; border-top: 1px solid #eaeaea; font-size: 12px; }
        th:first-child, td:first-child { width: 30px; padding-left: 0; }
        th:last-child, td:last-child { width: 32px; padding-right: 0; text-align: right; }
        tbody tr:hover { background: #fcfcfc; }
        input[type="checkbox"] { width: 15px; height: 15px; margin: 0; vertical-align: middle; accent-color: #202020; cursor: pointer; }
        .badge { display: inline-block; padding: 4px 9px; border: 1px solid #bee8c4; border-radius: 4px; background: #eaffed; color: #397b44; font-size: 11px; }
        .badge[data-status="Pending"] { border-color: #f0dfaa; background: #fff8e5; color: #8a681e; }
        .badge[data-status="Suspended"], .badge[data-status="Cancelled"] { border-color: #e0e0e0; background: #f3f3f3; color: #666; }
        .icon-button { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: 0; border-radius: 5px; background: transparent; color: #333; }
        .icon-button:hover { background: #efefef; }
        svg { width: 15px; height: 15px; flex-shrink: 0; }
        .table-footer { margin-top: 20px; color: #777; font-size: 11px; flex-wrap: wrap; }
        .table-footer p { margin: 0; }
        #empty-state { padding: 52px 12px; text-align: center; color: #777; }
        [hidden] { display: none !important; }
        dialog { width: min(620px, calc(100% - 32px)); max-height: calc(100vh - 48px); padding: 30px; border: 0; border-radius: 12px; color: #202020; }
        dialog::backdrop { background: rgb(0 0 0 / 28%); }
        .dialog-heading { margin-bottom: 8px; }
        h2 { margin: 0; font-size: 20px; font-weight: 600; }
        .dialog-description { margin: 0 0 26px; color: #777; font-size: 12px; line-height: 1.6; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 16px; }
        .field { display: grid; gap: 8px; font-size: 12px; }
        .field-wide { grid-column: 1 / -1; }
        .field input, .field select { width: 100%; min-width: 0; height: 40px; padding: 9px 11px; border: 1px solid #dedede; border-radius: 6px; background: #fff; color: #222; }
        .dialog-actions { justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        #delete-dialog { width: min(420px, calc(100% - 32px)); }
        #delete-dialog p { color: #666; line-height: 1.7; }
        @media (max-width: 1250px) { .users-panel { margin: 40px 28px; min-height: calc(100vh - 80px); } }
        @media (max-width: 600px) {
            .users-panel { margin: 20px 12px; padding: 20px 16px; min-height: calc(100vh - 40px); }
            .toolbar { align-items: flex-start; flex-direction: column; gap: 18px; }
            .actions { align-self: flex-end; }
            .form-grid { grid-template-columns: 1fr; }
            dialog { padding: 22px; }
            table { min-width: 790px; }
        }
    </style> -->
</head>
<body>
    <main class="users-panel" aria-labelledby="page-title">
        <header class="toolbar">
            <h1 id="page-title">Users</h1>
            <div class="actions">
                <button class="button button-danger" id="delete-users" type="button" disabled>
                    <span aria-hidden="true">−</span> Delete User
                </button>
                <button class="button button-dark" id="add-user" type="button">
                    <span aria-hidden="true">+</span> Add User
                </button>
            </div>
        </header>

        <div class="table-scroll">
            <table aria-label="Users and membership details">
                <thead>
                    <tr>
                        <th scope="col"><input id="select-all" type="checkbox" aria-label="Select all users"></th>
                        <th scope="col">ID</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Membership</th>
                        <th scope="col">Joined</th>
                        <th scope="col">Expiry</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col"><span hidden>Actions</span></th>
                    </tr>
                </thead>
                <tbody id="users-table"></tbody>
            </table>
            <p id="empty-state" hidden>No users yet. Add a user to get started.</p>
        </div>

        <footer class="table-footer">
            <p id="user-count" role="status" aria-live="polite"></p>
            <p>Sample users · Changes reset when you refresh.</p>
        </footer>
    </main>

    <template id="user-row">
        <tr>
            <td><input class="row-checkbox" type="checkbox"></td>
            <td data-field="id"></td>
            <td data-field="name"></td>
            <td data-field="membership"></td>
            <td data-field="joined"></td>
            <td data-field="expiry"></td>
            <td><span class="badge" data-field="status"></span></td>
            <td data-field="created"></td>
            <td>
                <button class="icon-button edit-user" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7" />
                        <path d="m16 3 5 5M10 14l-1 4 4-1L22 8a2 2 0 0 0-5-5Z" />
                    </svg>
                </button>
            </td>
        </tr>
    </template>

    <dialog id="user-dialog" aria-labelledby="dialog-title" aria-describedby="dialog-description">
        <div class="dialog-heading">
            <h2 id="dialog-title">Add User</h2>
            <button class="icon-button" type="button" data-close="user-dialog" aria-label="Close dialog">✕</button>
        </div>
        <p class="dialog-description" id="dialog-description">Enter the user's membership details.</p>
        <form id="user-form">
            <div class="form-grid">
                <label class="field field-wide">Full name
                    <input id="user-name" name="name" autocomplete="name" maxlength="100" required autofocus>
                </label>
                <label class="field">Member ID
                    <input id="user-id" name="id" maxlength="30" required>
                </label>
                <label class="field">Membership
                    <select id="user-membership" name="membership">
                        <option>Bronze</option>
                        <option>Silver</option>
                        <option>Gold</option>
                        <option>Platinum</option>
                    </select>
                </label>
                <label class="field">Joined
                    <input id="user-joined" name="joined" type="date" required>
                </label>
                <label class="field">Expiry (optional)
                    <input id="user-expiry" name="expiry" type="date">
                </label>
                <label class="field">Status
                    <select id="user-status" name="status">
                        <option>Active</option>
                        <option>Pending</option>
                        <option>Suspended</option>
                        <option>Cancelled</option>
                    </select>
                </label>
            </div>
            <div class="dialog-actions">
                <button class="button button-light" type="button" data-close="user-dialog">Cancel</button>
                <button class="button button-dark" id="save-user" type="submit">Add User</button>
            </div>
        </form>
    </dialog>

    <dialog id="delete-dialog" aria-labelledby="delete-title">
        <h2 id="delete-title">Delete selected users?</h2>
        <p>The selected users will be removed from this preview.</p>
        <div class="dialog-actions">
            <button class="button button-light" type="button" data-close="delete-dialog" autofocus>Cancel</button>
            <button class="button button-danger" id="confirm-delete" type="button">Delete</button>
        </div>
    </dialog>

    <script>
        const users = [
            { id: 'MEM-0001', name: 'Samantha Ching', membership: 'Platinum', joined: '2026-10-28', expiry: '2027-10-28', status: 'Active', created: '2026-10-28' },
            { id: 'MEM-0002', name: 'Alex Reyes', membership: 'Gold', joined: '2026-10-28', expiry: '2027-10-28', status: 'Active', created: '2026-10-28' },
        ];
        const table = document.querySelector('#users-table');
        const rowTemplate = document.querySelector('#user-row');
        const selectAll = document.querySelector('#select-all');
        const deleteButton = document.querySelector('#delete-users');
        const userDialog = document.querySelector('#user-dialog');
        const deleteDialog = document.querySelector('#delete-dialog');
        const form = document.querySelector('#user-form');
        const nameInput = document.querySelector('#user-name');
        const idInput = document.querySelector('#user-id');
        const joinedInput = document.querySelector('#user-joined');
        const expiryInput = document.querySelector('#user-expiry');
        let editingId = null;

        function syncSelection() {
            const selectedCount = table.querySelectorAll('.row-checkbox:checked').length;
            deleteButton.disabled = selectedCount === 0;
            selectAll.checked = users.length > 0 && selectedCount === users.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < users.length;
            selectAll.disabled = users.length === 0;
            document.querySelector('#user-count').textContent = `${users.length} ${users.length === 1 ? 'user' : 'users'}${selectedCount ? ` · ${selectedCount} selected` : ''}`;
        }

        function renderUsers() {
            table.replaceChildren();
            users.forEach((user) => {
                const row = rowTemplate.content.firstElementChild.cloneNode(true);
                row.querySelectorAll('[data-field]').forEach((cell) => {
                    cell.textContent = user[cell.dataset.field] || '—';
                });
                row.querySelector('.badge').dataset.status = user.status;
                const checkbox = row.querySelector('.row-checkbox');
                checkbox.value = user.id;
                checkbox.setAttribute('aria-label', `Select ${user.name} (${user.id})`);
                const editButton = row.querySelector('.edit-user');
                editButton.setAttribute('aria-label', `Edit ${user.name} (${user.id})`);
                editButton.addEventListener('click', () => openUserDialog(user));
                table.append(row);
            });
            document.querySelector('#empty-state').hidden = users.length > 0;
            syncSelection();
        }

        function openUserDialog(user = null) {
            form.reset();
            editingId = user?.id ?? null;
            form.querySelectorAll('input').forEach((input) => input.setCustomValidity(''));
            const today = new Date().toLocaleDateString('en-CA');
            const nextNumber = Math.max(0, ...users.map((item) => Number(item.id.replace(/^MEM-/, '')) || 0)) + 1;
            nameInput.value = user?.name ?? '';
            idInput.value = user?.id ?? `MEM-${String(nextNumber).padStart(4, '0')}`;
            form.elements.membership.value = user?.membership ?? 'Platinum';
            joinedInput.value = user?.joined ?? today;
            expiryInput.value = user?.expiry ?? '';
            form.elements.status.value = user?.status ?? 'Active';
            expiryInput.min = joinedInput.value;
            document.querySelector('#dialog-title').textContent = user ? 'Edit User' : 'Add User';
            document.querySelector('#save-user').textContent = user ? 'Save Changes' : 'Add User';
            userDialog.showModal();
        }

        document.querySelector('#add-user').addEventListener('click', () => openUserDialog());
        document.querySelectorAll('[data-close]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.close).close());
        });
        table.addEventListener('change', syncSelection);
        selectAll.addEventListener('change', () => {
            table.querySelectorAll('.row-checkbox').forEach((checkbox) => checkbox.checked = selectAll.checked);
            syncSelection();
        });
        joinedInput.addEventListener('input', () => expiryInput.min = joinedInput.value);
        form.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', () => input.setCustomValidity(''));
        });
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const id = idInput.value.trim();
            nameInput.setCustomValidity(nameInput.value.trim() ? '' : 'Enter a full name.');
            idInput.setCustomValidity(!id ? 'Enter a member ID.' : users.some((user) => user.id.toLowerCase() === id.toLowerCase() && user.id !== editingId) ? 'This member ID is already in use.' : '');
            if (!form.reportValidity()) {
                return;
            }
            const existingUser = users.find((user) => user.id === editingId);
            const user = {
                id,
                name: nameInput.value.trim(),
                membership: form.elements.membership.value,
                joined: joinedInput.value,
                expiry: expiryInput.value,
                status: form.elements.status.value,
                created: existingUser?.created ?? new Date().toLocaleDateString('en-CA'),
            };
            if (existingUser) {
                users[users.indexOf(existingUser)] = user;
            } else {
                users.push(user);
            }
            userDialog.close();
            renderUsers();
        });
        deleteButton.addEventListener('click', () => deleteDialog.showModal());
        document.querySelector('#confirm-delete').addEventListener('click', () => {
            const selectedIds = new Set([...table.querySelectorAll('.row-checkbox:checked')].map((checkbox) => checkbox.value));
            for (let index = users.length - 1; index >= 0; index--) {
                if (selectedIds.has(users[index].id)) {
                    users.splice(index, 1);
                }
            }
            deleteDialog.close();
            renderUsers();
        });
        renderUsers();
    </script>
</body>
</html>
