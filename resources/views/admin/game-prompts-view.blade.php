@extends('admin.layout')

@section('content')
@php
    function parsePromptNumber($str) {
        if (preg_match('/^(\D*)(\d+)(\D*)$/', $str, $matches)) {
            return [
                'prefix' => $matches[1],
                'number' => (int)$matches[2],
                'suffix' => $matches[3],
                'padding' => strlen($matches[2])
            ];
        }
        return [
            'prefix' => '',
            'number' => (int)$str,
            'suffix' => '',
            'padding' => strlen($str)
        ];
    }

    $start = parsePromptNumber($prompt->number_start);
    $end = parsePromptNumber($prompt->number_end);
@endphp

	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">
        @if($category)
            {{ $category->name }}
        @else
            Game Prompts
        @endif
        > {{ $prompt->prompt }}
      </span>
  </h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">
			<div class="d-flex justify-content-between mb-3">
                <div>
                    @if($category)
                    <button type="button" class="btn btn-info text-white me-2" id="copyAsTextBtn">
                        <i class="bi-clipboard me-1"></i> Copy as Text
                    </button>
                    <button type="button" class="btn btn-success" id="markAsDoneBtn">
                        <i class="bi-check-circle me-1"></i> Mark as Done
                    </button>
                    @endif
                </div>
                <div>
                    <a href="{{ url('panel/admin/game-prompts/export-pdf/'.$prompt->id) . ($category ? '?category_id='.$category->id : '') }}" class="btn btn-primary">
                        <i class="bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </div>

            @if (session('success_message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi-check-circle me-1"></i> {{ session('success_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div id="ajaxAlert" class="alert alert-dismissible fade" role="alert" style="display: none;">
                <i class="bi me-1" id="alertIcon"></i>
                <span id="alertMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">
                    <h6 class="mb-3">Active Orders</h6>
					<div class="table-responsive p-0">
						<table class="table table-hover" id="promptsTable">
                            <thead>
                                <tr>
                                    <th class="active">Ser</th>
                                    <th class="active">RTTP</th>
                                    <th class="active">First</th>
                                    <th class="active">Second</th>
                                    <th class="active">Action</th>
                                </tr>
                                <tr>
                                    <th class="active"></th>
                                    <th class="active">
                                        <input type="text" id="rttpSearch" class="form-control form-control-sm" placeholder="Search RTTP">
                                    </th>
                                    <th class="active">
                                        <input type="number" id="subtractFirst" class="form-control form-control-sm" placeholder="Sub First" style="width: 100px; max-width: 100%;">
                                    </th>
                                    <th class="active">
                                        <input type="number" id="subtractSecond" class="form-control form-control-sm" placeholder="Sub Second" style="width: 100px; max-width: 100%;">
                                    </th>
                                    <th class="active"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $counter = 1; @endphp
                                @for ($i = $start['number']; $i <= $end['number']; $i++)
                                @php
                                    $rttpValue = $start['prefix'] . str_pad($i, $start['padding'], '0', STR_PAD_LEFT) . $start['suffix'];
                                    // Check if this RTTP exists in the orders data
                                    if (!isset($ordersData[$rttpValue])) {
                                        continue;
                                    }
                                    $firstSum = $ordersData[$rttpValue]['first'];
                                    $secondSum = $ordersData[$rttpValue]['second'];
                                    $orderCount = $ordersData[$rttpValue]['count'];
                                    $isDuplicate = isset($exportedOrdersData[$rttpValue]);
                                @endphp
                                <tr class="rttp-row">
                                    <td>{{ $counter++ }}</td>
                                    <td class="rttp-cell">
                                        {{ $rttpValue }}
                                        @if($isDuplicate)
                                            <span class="badge bg-warning text-dark ms-1" title="Already exported">Duplicate</span>
                                        @endif
                                    </td>
                                    <td class="first-val" data-original="{{ $firstSum }}">{{ $firstSum }}</td>
                                    <td class="second-val" data-original="{{ $secondSum }}">{{ $secondSum }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success mark-single-done-btn" data-rttp="{{ $rttpValue }}">
                                            <i class="bi-check"></i> Done
                                        </button>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td colspan="2" class="text-end">Total:</td>
                                    <td id="totalFirst">0</td>
                                    <td id="totalSecond">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
						</table>
					</div>
				 </div>
			</div>

            @if(isset($exportedOrdersData))
            <div class="card shadow-custom border-0 mt-4" id="exportedOrdersCard" style="{{ count($exportedOrdersData) > 0 ? '' : 'display: none;' }}">
                <div class="card-body p-lg-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <i class="bi-check-circle-fill text-success me-2"></i>Exported Orders
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ url('panel/admin/game-prompts/export-exported-pdf/'.$prompt->id) . ($category ? '?category_id='.$category->id : '') }}" class="btn btn-sm btn-success">
                        <i class="bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-warning" id="markAllAsUndoneBtn">
                        <i class="bi-arrow-counterclockwise me-1"></i>Mark All as Undone
                    </button>
                </div>
            </div>
                    <div class="table-responsive p-0">
                        <table class="table table-hover" id="exportedOrdersTable">
                            <thead>
                                <tr>
                                    <th class="active">Ser</th>
                                    <th class="active">RTTP</th>
                                    <th class="active">First</th>
                                    <th class="active">Second</th>
                                    <th class="active">Subtracted Amount</th>
                                    <th class="active">Status</th>
                                    <th class="active">Action</th>
                                </tr>
                            </thead>
                            <tbody id="exportedOrdersBody">
                                @php $exportCounter = 1; @endphp
                                @foreach($exportedOrdersData as $rttpValue => $data)
                                <tr style="opacity: 0.6; background-color: rgba(25, 135, 84, 0.1);" data-rttp="{{ $rttpValue }}">
                                    <td>{{ $exportCounter++ }}</td>
                                    <td>{{ $rttpValue }}</td>
                                    <td>{{ $data['first'] }}</td>
                                    <td>{{ $data['second'] }}</td>
                                    <td>
                                        @if($data['cut_first'] || $data['cut_second'])
                                            <span style="color: #fd7e14; font-weight: 600; font-size: 0.95rem;">
                                                @if($data['cut_first'])
                                                    First: {{ $data['cut_first'] }}
                                                @endif
                                                @if($data['cut_first'] && $data['cut_second'])
                                                    <span class="mx-1">|</span>
                                                @endif
                                                @if($data['cut_second'])
                                                    Second: {{ $data['cut_second'] }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success">Done/Exported</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning mark-as-undone-btn" data-rttp="{{ $rttpValue }}">
                                            <i class="bi-arrow-counterclockwise me-1"></i>Mark as Undone
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @php
                                    $expTotalFirst = 0;
                                    $expTotalSecond = 0;
                                    foreach($exportedOrdersData as $data) {
                                        $expTotalFirst += $data['first'];
                                        $expTotalSecond += $data['second'];
                                    }
                                @endphp
                                <tr class="fw-bold" style="background-color: rgba(25, 135, 84, 0.2);" id="exportedTotalRow">
                                    <td colspan="2" class="text-end">Total:</td>
                                    <td id="expTotalFirst">{{ $expTotalFirst }}</td>
                                    <td id="expTotalSecond">{{ $expTotalSecond }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const promptId = "{{ $prompt->id }}";
    const categoryId = "{{ $category ? $category->id : '' }}";
    const inputFirst = document.getElementById('subtractFirst');
    const inputSecond = document.getElementById('subtractSecond');
    const markAsDoneBtn = document.getElementById('markAsDoneBtn');

    // Show alert function
    function showAlert(message, type = 'success') {
        const alertBox = document.getElementById('ajaxAlert');
        const alertMessage = document.getElementById('alertMessage');
        const alertIcon = document.getElementById('alertIcon');

        // Set message
        alertMessage.textContent = message;

        // Set icon and color based on type
        alertBox.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'show');
        if (type === 'success') {
            alertBox.classList.add('alert-success');
            alertIcon.className = 'bi bi-check-circle me-1';
        } else if (type === 'error') {
            alertBox.classList.add('alert-danger');
            alertIcon.className = 'bi bi-x-circle me-1';
        } else if (type === 'warning') {
            alertBox.classList.add('alert-warning');
            alertIcon.className = 'bi bi-exclamation-triangle me-1';
        }

        // Show alert
        alertBox.style.display = 'block';
        alertBox.classList.add('show');

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertBox.classList.remove('show');
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 150);
        }, 5000);
    }

    // Initial calculation and link update
    calculateValues();
    updateExportLink();

    // Event listeners
    if (inputFirst) {
        inputFirst.addEventListener('input', function() {
            calculateValues();
            updateExportLink();
        });
    }

    if (inputSecond) {
        inputSecond.addEventListener('input', function() {
            calculateValues();
            updateExportLink();
        });
    }

    // Mark as Done button
    if (markAsDoneBtn) {
        markAsDoneBtn.addEventListener('click', function() {
            const cutFirst = parseFloat(inputFirst.value) || 0;
            const cutSecond = parseFloat(inputSecond.value) || 0;

            if (!confirm('Are you sure you want to mark all orders as done? This action cannot be undone.')) {
                return;
            }

            // Disable button to prevent multiple clicks
            markAsDoneBtn.disabled = true;
            markAsDoneBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

            // Send AJAX request
            fetch('{{ route("admin.game_prompts.mark_as_done") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    prompt_id: promptId,
                    cut_first: cutFirst,
                    cut_second: cutSecond
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Reload the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('Error: ' + (data.message || 'Failed to mark orders as done'), 'error');
                    markAsDoneBtn.disabled = false;
                    markAsDoneBtn.innerHTML = '<i class="bi-check-circle me-1"></i> Mark as Done';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while processing your request', 'error');
                markAsDoneBtn.disabled = false;
                markAsDoneBtn.innerHTML = '<i class="bi-check-circle me-1"></i> Mark as Done';
            });
        });
    }

    // Copy as Text button
    const copyAsTextBtn = document.getElementById('copyAsTextBtn');
    if (copyAsTextBtn) {
        copyAsTextBtn.addEventListener('click', function() {
            const cutFirst = parseFloat(inputFirst.value) || 0;
            const cutSecond = parseFloat(inputSecond.value) || 0;
            let textToCopy = '';

            // Get all rows from the active orders table
            const rows = document.querySelectorAll('#promptsTable tbody tr');

            rows.forEach(row => {
                // Get RTTP - remove any badge text like "Duplicate"
                let rttpCell = row.querySelector('td:nth-child(2)');
                let rttp = rttpCell.childNodes[0].textContent.trim();

                // Get original values
                let firstCell = row.querySelector('.first-val');
                let secondCell = row.querySelector('.second-val');
                let originalFirst = parseFloat(firstCell.getAttribute('data-original')) || 0;
                let originalSecond = parseFloat(secondCell.getAttribute('data-original')) || 0;

                // Calculate final values (Original - Cut)
                // Ensure values don't go below 0
                let finalFirst = Math.max(0, originalFirst - cutFirst);
                let finalSecond = Math.max(0, originalSecond - cutSecond);

                // Only add if there's remaining value
                if (finalFirst > 0 || finalSecond > 0) {
                    // Format: RTTP f[First]s[Second]
                    // Example: 1234 f10s0
                    // Using Math.round to avoid decimals if needed, or keep decimals if required
                    textToCopy += `${rttp} f${finalFirst}s${finalSecond}\n`;
                }
            });

            if (textToCopy) {
                // Copy to clipboard
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showAlert('Copied to clipboard successfully!', 'success');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    showAlert('Failed to copy to clipboard', 'error');
                });
            } else {
                showAlert('No data to copy (all values might be 0 after subtraction)', 'warning');
            }
        });
    }

    function updateExportLink() {
        const subFirst = inputFirst ? inputFirst.value : '';
        const subSecond = inputSecond ? inputSecond.value : '';
        const exportBtn = document.querySelector('a[href*="export-pdf"]');

        if (exportBtn) {
            let url = new URL(exportBtn.href);
            if (subFirst) {
                url.searchParams.set('sub_first', subFirst);
            } else {
                url.searchParams.delete('sub_first');
            }

            if (subSecond) {
                url.searchParams.set('sub_second', subSecond);
            } else {
                url.searchParams.delete('sub_second');
            }

            exportBtn.href = url.toString();
        }
    }

    function calculateValues() {
        const subFirst = parseFloat(inputFirst ? inputFirst.value : 0) || 0;
        const subSecond = parseFloat(inputSecond ? inputSecond.value : 0) || 0;
        let totalFirst = 0;
        let totalSecond = 0;

        // Update First Column
        const firstCells = document.querySelectorAll('.first-val');
        firstCells.forEach(cell => {
            const original = parseFloat(cell.getAttribute('data-original')) || 0;
            let newVal = original;

            // Check if duplicate (look for badge in previous sibling cell)
            const rttpCell = cell.previousElementSibling;
            const isDuplicate = rttpCell && rttpCell.querySelector('.badge') !== null;

            // Subtract value but ensure it doesn't go below zero
            // Only subtract if NOT duplicate
            if (!isDuplicate && subFirst > 0) {
                newVal = Math.max(0, original - subFirst);
            }

            totalFirst += newVal;
            cell.textContent = newVal;
        });

        // Update Second Column
        const secondCells = document.querySelectorAll('.second-val');
        secondCells.forEach(cell => {
            const original = parseFloat(cell.getAttribute('data-original')) || 0;
            let newVal = original;

            // Check if duplicate (look for badge in previous sibling cell's previous sibling)
            // .second-val is after .first-val, which is after RTTP cell
            const firstValCell = cell.previousElementSibling;
            const rttpCell = firstValCell ? firstValCell.previousElementSibling : null;
            const isDuplicate = rttpCell && rttpCell.querySelector('.badge') !== null;

            // Subtract value but ensure it doesn't go below zero
            // Only subtract if NOT duplicate
            if (!isDuplicate && subSecond > 0) {
                newVal = Math.max(0, original - subSecond);
            }

            totalSecond += newVal;
            cell.textContent = newVal;
        });

        // Update Total Row
        const totalFirstCell = document.getElementById('totalFirst');
        const totalSecondCell = document.getElementById('totalSecond');

        if (totalFirstCell) totalFirstCell.textContent = totalFirst;
        if (totalSecondCell) totalSecondCell.textContent = totalSecond;
    }

    // RTTP Search Filter
    const rttpSearch = document.getElementById('rttpSearch');
    if (rttpSearch) {
        rttpSearch.addEventListener('input', function() {
            const filterValue = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#promptsTable tbody tr.rttp-row');

            rows.forEach(row => {
                const rttpCell = row.querySelector('.rttp-cell');
                if (rttpCell) {
                    const rttpText = rttpCell.textContent.toLowerCase().trim();
                    if (rttpText.includes(filterValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    }

    // Mark Single as Done functionality
    document.querySelectorAll('.mark-single-done-btn').forEach(button => {
        button.addEventListener('click', function() {
            const rttpValue = this.getAttribute('data-rttp');
            const cutFirst = parseFloat(inputFirst.value) || 0;
            const cutSecond = parseFloat(inputSecond.value) || 0;

            // Disable button
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            // Send AJAX request
            fetch('{{ route("admin.game_prompts.mark_as_done") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    prompt_id: promptId,
                    cut_first: cutFirst,
                    cut_second: cutSecond,
                    rttp: rttpValue // Send the specific RTTP
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');

                    // Animate and remove the row without reloading
                    const row = this.closest('tr');
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Recalculate totals after row removal
                        calculateValues();
                    }, 300);

                    // Add to exported table
                    if (data.exported_data) {
                        const exportedCard = document.getElementById('exportedOrdersCard');
                        const exportedBody = document.getElementById('exportedOrdersBody');
                        const expTotalFirstCell = document.getElementById('expTotalFirst');
                        const expTotalSecondCell = document.getElementById('expTotalSecond');

                        if (exportedCard) exportedCard.style.display = 'block';

                        Object.keys(data.exported_data).forEach(rttp => {
                            const item = data.exported_data[rttp];

                            // Check if row already exists (should not for single mark, but safety check)
                            const existingRow = exportedBody.querySelector(`tr[data-rttp="${rttp}"]`);
                            if (existingRow) return;

                            // Build HTML
                            const newRow = document.createElement('tr');
                            newRow.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
                            newRow.setAttribute('data-rttp', rttp);

                            // Calculate Ser (count rows + 1)
                            const ser = exportedBody.children.length + 1;

                            // Format cut string
                            let cutStr = '<span class="text-muted">-</span>';
                            if (item.cut_first > 0 || item.cut_second > 0) {
                                cutStr = '<span style="color: #fd7e14; font-weight: 600; font-size: 0.95rem;">';
                                if (item.cut_first > 0) cutStr += `First: ${item.cut_first}`;
                                if (item.cut_first > 0 && item.cut_second > 0) cutStr += '<span class="mx-1">|</span>';
                                if (item.cut_second > 0) cutStr += `Second: ${item.cut_second}`;
                                cutStr += '</span>';
                            }

                            newRow.innerHTML = `
                                <td>${ser}</td>
                                <td>${rttp}</td>
                                <td>${item.first}</td>
                                <td>${item.second}</td>
                                <td>${cutStr}</td>
                                <td><span class="badge bg-success">Done/Exported</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning mark-as-undone-btn-dynamic" data-rttp="${rttp}">
                                        <i class="bi-arrow-counterclockwise me-1"></i>Mark as Undone
                                    </button>
                                </td>
                            `;

                            exportedBody.appendChild(newRow);

                            // Update Totals
                            if (expTotalFirstCell) {
                                let current = parseFloat(expTotalFirstCell.textContent) || 0;
                                expTotalFirstCell.textContent = current + parseFloat(item.first);
                            }
                            if (expTotalSecondCell) {
                                let current = parseFloat(expTotalSecondCell.textContent) || 0;
                                expTotalSecondCell.textContent = current + parseFloat(item.second);
                            }

                            // Attach event listener
                            const newBtn = newRow.querySelector('.mark-as-undone-btn-dynamic');
                            if (newBtn) {
                                newBtn.addEventListener('click', function() {
                                    handleMarkAsUndone(this);
                                });
                            }
                        });
                    }
                } else {
                    showAlert('Error: ' + (data.message || 'Failed to mark as done'), 'error');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while processing your request', 'error');
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    });

    // Mark as Undone functionality
    function handleMarkAsUndone(button) {
        const rttpValue = button.getAttribute('data-rttp');

        if (!confirm(`Are you sure you want to mark RTTP "${rttpValue}" as undone?`)) {
            return;
        }

        // Disable button
        button.disabled = true;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        // Send AJAX request
        fetch('{{ route("admin.game_prompts.mark_as_undone") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                category_id: categoryId,
                rttp: rttpValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row with fade effect
                const row = button.closest('tr');
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    // Update Exported Totals before removing
                    const firstCell = row.querySelector('td:nth-child(3)');
                    const secondCell = row.querySelector('td:nth-child(4)');
                    const firstVal = parseFloat(firstCell.textContent) || 0;
                    const secondVal = parseFloat(secondCell.textContent) || 0;

                    const expTotalFirstCell = document.getElementById('expTotalFirst');
                    const expTotalSecondCell = document.getElementById('expTotalSecond');

                    if (expTotalFirstCell) {
                        let current = parseFloat(expTotalFirstCell.textContent) || 0;
                        expTotalFirstCell.textContent = Math.max(0, current - firstVal);
                    }
                    if (expTotalSecondCell) {
                        let current = parseFloat(expTotalSecondCell.textContent) || 0;
                        expTotalSecondCell.textContent = Math.max(0, current - secondVal);
                    }

                    row.remove();
                    // Check if there are no more exported orders
                    const tbody = document.getElementById('exportedOrdersBody');
                    if (!tbody || tbody.children.length === 0) {
                        // Hide the entire exported orders section
                        const exportedCard = document.getElementById('exportedOrdersCard');
                        if (exportedCard) {
                            exportedCard.style.display = 'none';
                        }
                    }
                    // Show success message
                    showAlert(data.message, 'success');
                    // Reload to update active orders (still need reload to bring back to active list properly without complex logic)
                    // Or we could reconstruct the active row, but reload is safer/easier for "Undone"
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }, 300);
            } else {
                showAlert('Error: ' + (data.message || 'Failed to mark as undone'), 'error');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while processing your request', 'error');
            button.disabled = false;
            button.innerHTML = originalHTML;
        });
    }

    document.querySelectorAll('.mark-as-undone-btn').forEach(button => {
        button.addEventListener('click', function() {
            handleMarkAsUndone(this);
        });
    });

    // Mark All as Undone functionality
    const markAllAsUndoneBtn = document.getElementById('markAllAsUndoneBtn');
    if (markAllAsUndoneBtn) {
        markAllAsUndoneBtn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to mark ALL exported orders as undone? This will restore all orders for this prompt.')) {
                return;
            }

            // Disable button
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

            // Send AJAX request
            fetch('{{ route("admin.game_prompts.mark_all_as_undone") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    prompt_id: promptId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Reload the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('Error: ' + (data.message || 'Failed to mark all as undone'), 'error');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while processing your request', 'error');
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    }
});
</script>

<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1030;">
    <div class="d-flex flex-column gap-2">
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="btn btn-dark rounded-circle shadow" title="Go to Top" style="width: 45px; height: 45px;">
            <i class="bi bi-arrow-up"></i>
        </button>
        <button onclick="window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'})" class="btn btn-dark rounded-circle shadow" title="Go to Bottom" style="width: 45px; height: 45px;">
            <i class="bi bi-arrow-down"></i>
        </button>
    </div>
</div>
@endsection
