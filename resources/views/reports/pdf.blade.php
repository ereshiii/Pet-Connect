<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($report_type) }} Report - {{ $clinic->clinic_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
        }
        
        .header h1 {
            font-size: 28px;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 18px;
            color: #666;
            font-weight: normal;
        }
        
        .header .period {
            margin-top: 10px;
            font-size: 14px;
            color: #888;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #E5E7EB;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .metric-card {
            padding: 15px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
        }
        
        .metric-label {
            font-size: 11px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .metric-value {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }
        
        .metric-subtitle {
            font-size: 10px;
            color: #9CA3AF;
            margin-top: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead {
            background: #4F46E5;
            color: white;
        }
        
        table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        table tbody tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-completed { background: #D1FAE5; color: #065F46; }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-confirmed { background: #DBEAFE; color: #1E40AF; }
        .status-cancelled { background: #FEE2E2; color: #991B1B; }
        .status-no_show { background: #E5E7EB; color: #374151; }
        .status-paid { background: #D1FAE5; color: #065F46; }
        .status-overdue { background: #FEE2E2; color: #991B1B; }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            color: #9CA3AF;
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-green { color: #10B981; }
        .text-red { color: #EF4444; }
        .text-yellow { color: #F59E0B; }
        
        .summary-box {
            background: #F0F9FF;
            border: 2px solid #DBEAFE;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #DBEAFE;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $clinic->clinic_name }}</h1>
        <h2>{{ ucfirst($report_type) }} Report</h2>
        <p class="period">
            Period: {{ $start_date->format('F d, Y') }} - {{ $end_date->format('F d, Y') }}
        </p>
    </div>

    <!-- Key Metrics (if comprehensive or analytics) -->
    @if(isset($data['analytics']))
    <div class="section">
        <div class="section-title">Key Performance Metrics</div>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Patients</div>
                <div class="metric-value">{{ number_format($data['analytics']['total_patients']) }}</div>
                <div class="metric-subtitle">
                    {{ $data['analytics']['monthly_growth'] > 0 ? '+' : '' }}{{ number_format($data['analytics']['monthly_growth'], 1) }}% from last month
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Avg Visit Value</div>
                <div class="metric-value">₱{{ number_format($data['analytics']['average_visit_value'], 2) }}</div>
                <div class="metric-subtitle">Per appointment revenue</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Patient Retention</div>
                <div class="metric-value">{{ number_format($data['analytics']['patient_retention'], 1) }}%</div>
                <div class="metric-subtitle">Returning patients rate</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Completion Rate</div>
                <div class="metric-value">{{ number_format($data['analytics']['appointment_completion'], 1) }}%</div>
                <div class="metric-subtitle">{{ number_format($data['analytics']['no_show_rate'], 1) }}% no-show rate</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Financial Summary -->
    @if(isset($data['financial_summary']))
    <div class="section">
        <div class="section-title">Financial Summary</div>
        <div class="summary-box">
            <div class="summary-row">
                <strong>Total Revenue</strong>
                <span class="text-green"><strong>₱{{ number_format($data['financial_summary']['total_revenue'], 2) }}</strong></span>
            </div>
            <div class="summary-row">
                <span>Pending Amount</span>
                <span class="text-yellow">₱{{ number_format($data['financial_summary']['pending_amount'], 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Overdue Amount</span>
                <span class="text-red">₱{{ number_format($data['financial_summary']['overdue_amount'], 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Invoices</span>
                <span>{{ $data['financial_summary']['total_invoices'] }}</span>
            </div>
            <div class="summary-row">
                <span>Paid Invoices</span>
                <span class="text-green">{{ $data['financial_summary']['paid_invoices'] }}</span>
            </div>
            <div class="summary-row">
                <span>Collection Rate</span>
                <span><strong>{{ number_format($data['financial_summary']['collection_rate'], 1) }}%</strong></span>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Services -->
    @if(isset($data['top_services']) && count($data['top_services']) > 0)
    <div class="section">
        <div class="section-title">Top Services</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Service Name</th>
                    <th class="text-right">Appointments</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_services'] as $index => $service)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $service['service_name'] }}</strong></td>
                    <td class="text-right">{{ $service['count'] }}</td>
                    <td class="text-right">₱{{ number_format($service['revenue'], 2) }}</td>
                    <td class="text-right">{{ number_format($service['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Patients List -->
    @if(isset($data['patients']) && count($data['patients']) > 0)
    <div class="section">
        <div class="section-title">Patient Records ({{ count($data['patients']) }} total)</div>
        <table>
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Owner</th>
                    <th class="text-right">Visits</th>
                    <th>Last Visit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['patients'] as $patient)
                <tr>
                    <td><strong>{{ $patient['pet_name'] }}</strong></td>
                    <td>{{ $patient['species'] }}</td>
                    <td>{{ $patient['breed'] }}</td>
                    <td>{{ $patient['owner_name'] }}</td>
                    <td class="text-right">{{ $patient['appointment_count'] }}</td>
                    <td>{{ $patient['last_visit'] ? \Carbon\Carbon::parse($patient['last_visit'])->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Appointments List -->
    @if(isset($data['appointments']) && count($data['appointments']) > 0)
    <div class="section">
        <div class="section-title">Appointment Records ({{ count($data['appointments']) }} total)</div>
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Pet</th>
                    <th>Owner</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th class="text-right">Duration</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['appointments'] as $apt)
                <tr>
                    <td>{{ $apt['date'] }}</td>
                    <td><strong>{{ $apt['pet_name'] }}</strong></td>
                    <td>{{ $apt['owner_name'] }}</td>
                    <td>{{ $apt['service'] }}</td>
                    <td><span class="status-badge status-{{ $apt['status'] }}">{{ $apt['status'] }}</span></td>
                    <td class="text-right">{{ $apt['duration'] }} min</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Invoices List -->
    @if(isset($data['invoices']) && count($data['invoices']) > 0)
    <div class="section">
        <div class="section-title">Invoice Records ({{ count($data['invoices']) }} total)</div>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['invoices'] as $invoice)
                <tr>
                    <td><strong>{{ $invoice['invoice_number'] }}</strong></td>
                    <td>{{ $invoice['date'] }}</td>
                    <td>{{ $invoice['client'] }}</td>
                    <td class="text-right">₱{{ number_format($invoice['total_amount'], 2) }}</td>
                    <td class="text-right">₱{{ number_format($invoice['balance_due'], 2) }}</td>
                    <td><span class="status-badge status-{{ $invoice['status'] }}">{{ $invoice['status'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Appointment Statistics -->
    @if(isset($data['appointment_stats']))
    <div class="section">
        <div class="section-title">Appointment Statistics</div>
        <div class="summary-box">
            <div class="summary-row">
                <strong>Total Appointments</strong>
                <strong>{{ $data['appointment_stats']['total_appointments'] }}</strong>
            </div>
            <div class="summary-row">
                <span>Completed</span>
                <span class="text-green">{{ $data['appointment_stats']['completed'] }}</span>
            </div>
            <div class="summary-row">
                <span>Confirmed</span>
                <span>{{ $data['appointment_stats']['confirmed'] }}</span>
            </div>
            <div class="summary-row">
                <span>Pending</span>
                <span>{{ $data['appointment_stats']['pending'] }}</span>
            </div>
            <div class="summary-row">
                <span>Cancelled</span>
                <span class="text-red">{{ $data['appointment_stats']['cancelled'] }}</span>
            </div>
            <div class="summary-row">
                <span>No Show</span>
                <span>{{ $data['appointment_stats']['no_show'] }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Patient Statistics -->
    @if(isset($data['patient_stats']))
    <div class="section">
        <div class="section-title">Patient Statistics</div>
        <div class="summary-box">
            <div class="summary-row">
                <strong>Total Patients</strong>
                <strong>{{ $data['patient_stats']['total_patients'] }}</strong>
            </div>
            <div class="summary-row">
                <span>New Patients</span>
                <span class="text-green">{{ $data['patient_stats']['new_patients'] }}</span>
            </div>
            <div class="summary-row">
                <span>Returning Patients</span>
                <span>{{ $data['patient_stats']['returning_patients'] }}</span>
            </div>
            <div class="summary-row">
                <span>Average Visits per Patient</span>
                <span>{{ number_format($data['patient_stats']['average_visits_per_patient'], 1) }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
        <p>{{ $clinic->clinic_name }} - Clinic Management System</p>
    </div>
</body>
</html>
