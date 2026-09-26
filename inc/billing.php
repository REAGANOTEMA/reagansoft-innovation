<?php
/**
 * ============================================================
 * PAYMENT DETAILS GATE  (inc/billing.php)
 * ============================================================
 * A client may not hand over money until we know who they are and
 * where to put the paperwork. This file is the single place that
 * decides whether that is true.
 *
 * WHY A GATE AND NOT A HINT
 * Deposits and invoices are reconciled by hand, by a human, against a
 * bank or mobile-money statement. A payment from an account we cannot
 * name, tax or trace is a payment nobody can answer a query about
 * three weeks later. Collecting the details up front is what makes the
 * invoice, the receipt and the contract all agree.
 *
 * THE ONE RULE
 * Every field the gate needs is described once, in
 * billing_requirements(). The completeness check, the progress bar,
 * the on-page checklist, the validation and the rendered form are all
 * generated from that one list, so they cannot drift apart and a new
 * field only has to be added in one place.
 *
 * WHERE IT IS ENFORCED
 *   checkout.php                  the deposit that starts a project
 *   client/project.php            paying an invoice
 * Both call billing_require_complete() before accepting money, and
 * both are refused with a link to client/billing.php.
 *
 * NOT A NEW TABLE
 * These columns live on `users` rather than in a billing table,
 * because a client has exactly one payer identity for their whole
 * relationship with us. A second table would only add a join to every
 * read of a client name.
 * ============================================================
 */

/* ------------------------------------------------------------------
 * Countries
 *
 * Uganda first, then the region we actually trade with, then the
 * diaspora markets our clients live in. 'Other' is offered rather
 * than left off, so nobody is boxed into a list — the gate only cares
 * that the field was answered deliberately.
 * ------------------------------------------------------------------ */
function billing_countries(): array
{
    return [
        'Uganda', 'Kenya', 'Tanzania', 'Rwanda', 'Burundi', 'South Sudan',
        'DR Congo', 'Ethiopia', 'Nigeria', 'Somalia', 'India', 'United Kingdom',
        'United States', 'Canada', 'South Africa', 'Other',
    ];
}

/* ------------------------------------------------------------------
 * Is this client paying as a business?
 *
 * A sole trader, a school, a church or a person paying for their own
 * site has no TIN to give. Demanding one from them would block a
 * paying customer over a field that does not apply to them, so the
 * 'individual' branch exists purely to stop that.
 * ------------------------------------------------------------------ */
function billing_is_business(array $user): bool
{
    return (string)($user['payer_type'] ?? 'business') !== 'individual';
}

/* ------------------------------------------------------------------
 * Are the gate's columns present?
 *
 * An install that predates the gate has no such columns, and the
 * obvious failure mode is to treat "column absent" as "nothing
 * missing" — which would quietly switch the gate OFF for exactly the
 * clients who need it. So an absent column is reported as NOT ready,
 * and the pages show an administrator-facing note instead of a form.
 *
 * One query for all six, not six queries, and cached for the request.
 * ------------------------------------------------------------------ */
function billing_schema_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    $columns = billing_required_columns();
    try {
        $st = db()->prepare(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN (?,?,?,?,?,?)'
        );
        $st->execute(array_merge(['users'], $columns));
        $ready = count($st->fetchAll(PDO::FETCH_COLUMN)) === count($columns);
    } catch (Throwable $e) {
        // Some shared hosts refuse information_schema outright. Fall
        // back to asking for the columns by name, one query each. Slower
        // but it is the only question those hosts allow.
        $found = 0;
        try {
            foreach ($columns as $col) {
                $q = db()->prepare(
                    'SELECT COUNT(*) FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
                );
                $q->execute(['users', $col]);
                $found += (int)$q->fetchColumn();
            }
            $ready = $found === count($columns);
        } catch (Throwable $e2) {
            $ready = false;
        }
    }

    return $ready;
}

/* ------------------------------------------------------------------
 * The gate's column list, in one place for the readiness probe.
 * ------------------------------------------------------------------ */
function billing_required_columns(): array
{
    return ['payer_type', 'tax_id', 'national_id', 'country', 'city', 'billing_terms_at'];
}

/* ------------------------------------------------------------------
 * THE REQUIREMENT LIST
 *
 * One entry per field, each carrying everything both the validation
 * and the UI need: what to call it, why we want it, what shape it
 * takes, and whether it is required for this particular client.
 *
 * `required` is computed rather than hard-coded, because it depends on
 * the payer type — see billing_is_business().
 * ------------------------------------------------------------------ */
function billing_requirements(array $user): array
{
    $isBusiness = billing_is_business($user);
    $name = trim((string)($user['full_name'] ?? ''));
    $company = trim((string)($user['company'] ?? ''));

    return [
        /* ---- 1. How we reach you ---- */
        [
            'key' => 'full_name', 'group' => 'contact', 'type' => 'text',
            'label' => 'Full name', 'value' => $name, 'max' => 150,
            'required' => true, 'autocomplete' => 'name',
            'placeholder' => 'e.g. Amara Kaggwa',
            'why' => 'The name we put on your invoice and quotation.',
        ],
        [
            'key' => 'email', 'group' => 'contact', 'type' => 'email',
            'label' => 'Email address', 'value' => trim((string)($user['email'] ?? '')), 'max' => 190,
            'required' => true, 'autocomplete' => 'email',
            'placeholder' => 'e.g. you@company.co.ug',
            'why' => 'Your invoice, receipt and project updates are sent here.',
        ],
        [
            'key' => 'phone', 'group' => 'contact', 'type' => 'tel',
            'label' => 'Mobile number', 'value' => trim((string)($user['phone'] ?? '')), 'max' => 40,
            'required' => true, 'autocomplete' => 'tel',
            'placeholder' => 'e.g. 0772 123 456',
            'why' => 'We confirm every payment by phone, so a wrong number means a delayed project.',
            'hint' => 'We call and send Mobile Money prompts to this number.',
        ],

        /* ---- 2. Who is paying ---- */
        [
            'key' => 'payer_type', 'group' => 'identity', 'type' => 'choice',
            'label' => 'I am paying as', 'value' => $isBusiness ? 'business' : 'individual',
            'required' => true,
            'why' => 'This decides the rest of the questions below.',
        ],
        [
            'key' => 'company', 'group' => 'identity', 'type' => 'text',
            'label' => 'Registered business name', 'value' => $company, 'max' => 190,
            'required' => $isBusiness, 'business_only' => true, 'autocomplete' => 'organization',
            'placeholder' => 'e.g. Kireka Farm Supplies Ltd',
            'why' => 'The legal name on the invoice. Not your trading name if they differ.',
        ],
        [
            'key' => 'tax_id', 'group' => 'identity', 'type' => 'text',
            'label' => 'Tax Identification Number (TIN)',
            'value' => trim((string)($user['tax_id'] ?? '')), 'max' => 60,
            'required' => $isBusiness, 'business_only' => true, 'autocomplete' => 'off',
            'placeholder' => 'e.g. 1002456789',
            'why' => 'Issued by Uganda Revenue Authority. It lets us raise a tax invoice you can claim.',
            'hint' => 'On your URA PIN card. Businesses only — leave blank if you are paying as an individual.',
        ],
        [
            'key' => 'national_id', 'group' => 'identity', 'type' => 'text',
            'label' => 'National ID or passport number', 'value' => trim((string)($user['national_id'] ?? '')), 'max' => 60,
            'required' => false, 'autocomplete' => 'off',
            'placeholder' => 'e.g. 256754321098',
            'why' => 'Optional, but it lets us settle a payment query without phoning you.',
            'hint' => 'Optional. Your NIN or passport number.',
        ],

        /* ---- 3. Where you are ---- */
        [
            'key' => 'address', 'group' => 'location', 'type' => 'text',
            'label' => 'Physical address', 'value' => trim((string)($user['address'] ?? '')), 'max' => 255,
            'required' => true, 'autocomplete' => 'street-address',
            'placeholder' => 'e.g. Plot 14, Nalufenya',
            'why' => 'Contracts, deliverables and hardware are delivered here.',
        ],
        [
            'key' => 'city', 'group' => 'location', 'type' => 'text',
            'label' => 'District or town', 'value' => trim((string)($user['city'] ?? '')), 'max' => 90,
            'required' => true, 'autocomplete' => 'address-level2',
            'placeholder' => 'e.g. Jinja',
            'why' => 'Tells us where to deliver, and which region your project belongs to.',
        ],
        [
            'key' => 'country', 'group' => 'location', 'type' => 'select',
            'label' => 'Country', 'value' => trim((string)($user['country'] ?? '')) ?: 'Uganda', 'max' => 80,
            'required' => true, 'options' => billing_countries(),
            'why' => 'Used on the invoice and to work out the right way to take your payment.',
        ],
    ];
}

/* ------------------------------------------------------------------
 * The terms tick, kept apart from the field list because it is not a
 * stored value — it is the moment the client agreed.
 * ------------------------------------------------------------------ */
function billing_terms_accepted(array $user): bool
{
    return trim((string)($user['billing_terms_at'] ?? '')) !== '';
}

/* ------------------------------------------------------------------
 * Which required fields are still outstanding?
 *
 * The only thing that decides the gate. Returns a list of
 * ['key' => ..., 'label' => ..., 'why' => ...] so the caller can show
 * the client exactly what is left instead of a bare "incomplete".
 * ------------------------------------------------------------------ */
function billing_missing(array $user): array
{
    $out = [];
    foreach (billing_requirements($user) as $item) {
        if (empty($item['required'])) {
            continue;
        }
        if (trim((string)$item['value']) === '') {
            $out[] = ['key' => $item['key'], 'label' => $item['label'], 'why' => (string)($item['why'] ?? '')];
        }
    }
    if (!billing_terms_accepted($user)) {
        $out[] = [
            'key' => 'terms',
            'label' => 'Accept the payment terms',
            'why' => 'One tick that says the billing details above are correct and may be used on your invoice.',
        ];
    }
    return $out;
}

/* ------------------------------------------------------------------
 * Has the client passed the gate?
 * ------------------------------------------------------------------ */
function billing_complete(array $user): bool
{
    return billing_schema_ready() && billing_missing($user) === [];
}

/* ------------------------------------------------------------------
 * How far along they are, 0-100, for the progress meter.
 *
 * Counts REQUIRED fields only, using the same predicate as
 * billing_missing(). That is the point: the meter and the gate have to
 * be reading the same list, or the bar promises a 100% that the gate
 * will not honour. Counting optional fields too would be the bug this
 * exacts — an individual who has no TIN and no NIN could fill in every
 * question asked of them and still be told they were not done.
 *
 * The terms tick is counted as one more step, because it is the last
 * thing standing between them and paying.
 * ------------------------------------------------------------------ */
function billing_progress(array $user): array
{
    $done = 0;
    $total = 0;
    foreach (billing_requirements($user) as $item) {
        if (empty($item['required'])) {
            continue;
        }
        $total++;
        if (trim((string)$item['value']) !== '') {
            $done++;
        }
    }

    $total++;
    if (billing_terms_accepted($user)) {
        $done++;
    }

    return [
        'done' => $done,
        'total' => $total,
        'percent' => $total > 0 ? (int)round($done / $total * 100) : 0,
    ];
}

/* ------------------------------------------------------------------
 * Validate a submitted form.
 *
 * Returns [errors, clean]. `clean` holds the values to store, already
 * trimmed, with the fields that do not apply to this payer type left
 * out entirely rather than written as blanks.
 *
 * Errors are written as full sentences addressed to the client, and
 * each is anchored to a field key so the form can mark the input.
 * ------------------------------------------------------------------ */
function billing_validate(array $user, array $post): array
{
    $errors = [];
    $clean = [];

    $get = static function (string $key) use ($post): string {
        return trim((string)($post[$key] ?? ''));
    };

    $payerType = (string)($post['payer_type'] ?? 'business');
    if (!in_array($payerType, ['business', 'individual'], true)) {
        $payerType = 'business';
    }
    $isBusiness = $payerType === 'business';

    $fullName = $get('full_name');
    if ($fullName === '') {
        $errors['full_name'] = 'Please enter your full name — it goes on your invoice.';
    } elseif (mb_strlen($fullName) > 150) {
        $errors['full_name'] = 'That name is too long. Please use 150 characters or fewer.';
    } else {
        $clean['full_name'] = $fullName;
    }

    $email = strtolower($get('email'));
    if ($email === '') {
        $errors['email'] = 'Please enter an email address so we can send your invoice and receipt.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That email address does not look right. Check for a missing @ or a typo.';
    } elseif (mb_strlen($email) > 190) {
        $errors['email'] = 'That email address is too long.';
    } elseif (strcasecmp($email, (string)($user['email'] ?? '')) !== 0) {
        // This form can change the login email, so it has to carry the
        // same duplicate check client/profile.php does. Without it the
        // UNIQUE index on users.email turns into a raw SQL error on a
        // field the client believed they had filled in correctly.
        $errors['email'] = 'That email address is already in use by another account.';
        try {
            $st = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $st->execute([$email, (int)($user['id'] ?? 0)]);
            if (!$st->fetch()) {
                unset($errors['email']);
                $clean['email'] = $email;
            }
        } catch (Throwable $e) {
            // The uniqueness index still has our back, so treat an
            // unavailable check as "let the save try" rather than
            // blocking a legitimate client over a lookup we could not run.
        }
    } else {
        $clean['email'] = $email;
    }

    $phone = $get('phone');
    if ($phone === '') {
        $errors['phone'] = 'Please enter a mobile number. We use it to confirm your payment.';
    } else {
        $digits = phone_digits($phone);
        // Nine digits is the shortest number that can be dialled in
        // Uganda. Anything shorter is a typo, and a typo here is the
        // single most common reason a payment sits unconfirmed.
        if (strlen($digits) < 9) {
            $errors['phone'] = 'That number looks too short. Enter it as 0772 123 456 or +256772123456.';
        } elseif (strlen($digits) > 15) {
            $errors['phone'] = 'That number is too long. Enter it as 0772 123 456 or +256772123456.';
        } elseif (mb_strlen($phone) > 40) {
            $errors['phone'] = 'That number is too long.';
        } else {
            // Keep what they typed. It is what they will recognise on
            // their own statement, and phone_display() normalises it for
            // us at the point of reading, so there is nothing to gain by
            // rewriting their number behind their back.
            $clean['phone'] = $phone;
        }
    }

    $clean['payer_type'] = $payerType;

    // Company and TIN are only demanded from a business. When someone
    // switches to 'individual' the browser hides those inputs, so they
    // are not posted and we must carry the stored value forward
    // instead of wiping it — a sole trader is not permanently an
    // individual, and silently deleting their trading name would be a
    // nasty surprise.
    $posted = static fn (string $k): bool => array_key_exists($k, $post);

    if ($isBusiness) {
        $company = $get('company');
        if ($company === '') {
            $errors['company'] = 'Please enter your registered business name for the invoice.';
        } elseif (mb_strlen($company) > 190) {
            $errors['company'] = 'That business name is too long.';
        } else {
            $clean['company'] = $company;
        }

        $taxId = strtoupper(preg_replace('/\s+/', '', $get('tax_id')) ?? '');
        if ($taxId === '') {
            $errors['tax_id'] = 'Please enter your TIN so we can raise a tax invoice you can claim.';
        } elseif (mb_strlen($taxId) > 60) {
            $errors['tax_id'] = 'That TIN is too long.';
        } elseif (preg_match('/^[A-Z0-9\-\/]{6,20}$/', $taxId) !== 1) {
            // Deliberately loose. URA TINs are 10 digits, but the
            // prefix letter varies by taxpayer class and we would
            // rather accept an odd one and query it than turn away a
            // paying client over a formatting rule.
            $errors['tax_id'] = 'Please enter your TIN using letters and numbers only, e.g. 1002456789.';
        } else {
            $clean['tax_id'] = $taxId;
        }
    } else {
        if ($posted('company')) {
            $company = $get('company');
            $clean['company'] = $company === '' ? null : (mb_strlen($company) > 190 ? (string)($user['company'] ?? '') : $company);
        }
        if ($posted('tax_id')) {
            $taxId = strtoupper(preg_replace('/\s+/', '', $get('tax_id')) ?? '');
            $clean['tax_id'] = $taxId === '' ? null : $taxId;
        }
    }

    $nationalId = strtoupper(preg_replace('/\s+/', '', $get('national_id')) ?? '');
    if ($nationalId !== '' && preg_match('/^[A-Z0-9]{6,20}$/', $nationalId) !== 1) {
        $errors['national_id'] = 'Please enter your NIN or passport number using letters and numbers only, or leave it blank.';
    } else {
        $clean['national_id'] = $nationalId === '' ? null : $nationalId;
    }

    $address = $get('address');
    if ($address === '') {
        $errors['address'] = 'Please enter your physical address so we know where to deliver.';
    } elseif (mb_strlen($address) > 255) {
        $errors['address'] = 'That address is too long. Please use 255 characters or fewer.';
    } else {
        $clean['address'] = $address;
    }

    $city = $get('city');
    if ($city === '') {
        $errors['city'] = 'Please enter your district or town.';
    } elseif (mb_strlen($city) > 90) {
        $errors['city'] = 'That district or town name is too long.';
    } else {
        $clean['city'] = $city;
    }

    $country = $get('country');
    if ($country === '') {
        $errors['country'] = 'Please choose your country.';
    } elseif (!in_array($country, billing_countries(), true)) {
        $errors['country'] = 'Please choose your country from the list.';
    } elseif (mb_strlen($country) > 80) {
        $errors['country'] = 'That country name is too long.';
    } else {
        $clean['country'] = $country;
    }

    // The terms tick is demanded the first time, and again after a change
    // to a field that actually reaches an invoice. The billing page tells
    // the client they will only be asked again "if you change them", so the
    // two have to agree. Re-asking on a purely cosmetic edit would train
    // clients to tick boxes without reading, which defeats the point of
    // having one at all, so only the payer-identity fields below count.
    // national_id is left out on purpose: it is optional and never printed
    // on an invoice.
    $material = ['full_name', 'email', 'phone', 'payer_type', 'company', 'tax_id', 'address', 'city', 'country'];
    $changed = false;
    foreach ($material as $key) {
        if (!array_key_exists($key, $clean)) {
            continue;
        }
        if (trim((string)($user[$key] ?? '')) !== trim((string)$clean[$key])) {
            $changed = true;
            break;
        }
    }

    if ($changed || !billing_terms_accepted($user)) {
        if (empty($post['terms_agree'])) {
            $errors['terms'] = $changed
                ? 'You changed the details we put on your invoice, so please confirm them again.'
                : 'Please confirm the details above are correct, so we can use them on your invoice.';
        } else {
            $clean['billing_terms_at'] = date('Y-m-d H:i:s');
        }
    }

    return [$errors, $clean];
}

/* ------------------------------------------------------------------
 * Store a validated set of payment details.
 *
 * Refuses loudly when the columns are absent rather than letting a
 * bare SQL error surface, so the page can tell an administrator what
 * to run.
 * ------------------------------------------------------------------ */
function billing_save(PDO $pdo, array $user, array $clean): void
{
    if (!billing_schema_ready()) {
        throw new RuntimeException('Payment details columns are missing from the users table. Run database/upgrade.sql.');
    }

    $sets = [];
    $args = [];
    foreach (['full_name', 'email', 'phone', 'payer_type', 'company', 'tax_id', 'national_id', 'address', 'city', 'country', 'billing_terms_at'] as $field) {
        if (!array_key_exists($field, $clean)) {
            continue;
        }
        $sets[] = "`$field` = ?";
        $value = $clean[$field];
        $args[] = ($value === '' || $value === null) && in_array($field, ['company', 'tax_id', 'national_id'], true) ? null : $value;
    }
    if (!$sets) {
        return;
    }

    $args[] = (int)$user['id'];
    try {
        $pdo->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
    } catch (PDOException $e) {
        // billing_validate() already checks for a taken email, but two
        // people can submit at the same moment. Let the caller show a
        // retryable message instead of the generic error page, and say
        // nothing about SQL in the process.
        if ((string)$e->getCode() === '23000' && stripos($e->getMessage(), 'email') !== false) {
            throw new RuntimeException('That email address was just taken by another account. Please use a different one.');
        }
        throw $e;
    }

    // A client's session caches their name in the header, so it has to
    // follow the change they just made.
    if (isset($clean['full_name'])) {
        $_SESSION['name'] = $clean['full_name'];
    }
}

/* ------------------------------------------------------------------
 * A safe place to come back to.
 *
 * Only same-site absolute paths. `//evil.example` is rejected as well
 * as `https://evil.example`, because browsers treat a protocol-relative
 * URL as a different origin — that is the whole trick, and checking
 * only for a leading slash would wave it straight through.
 * ------------------------------------------------------------------ */
function billing_safe_next(?string $next, string $fallback): string
{
    $next = trim((string)$next);
    if ($next === '') {
        return $fallback;
    }
    if (str_starts_with($next, '//') || preg_match('#^[a-z][a-z0-9+.\-]*:#i', $next)) {
        return $fallback;
    }
    if (!str_starts_with($next, '/')) {
        return $fallback;
    }
    return $next;
}

/* ------------------------------------------------------------------
 * Where the client is right now, as a path this app can redirect to.
 *
 * REQUEST_URI is used rather than a rebuilt app_url() because it
 * already carries the deployment's base path — the app may live in a
 * subdirectory, in which case a hand-built '/client/project.php'
 * would point outside the app. billing_safe_next() then vets it, which
 * also disposes of a crafted request target such as '//evil.host/x'.
 * ------------------------------------------------------------------ */
function billing_return_here(string $fallback = ''): string
{
    $fallback = $fallback !== '' ? $fallback : app_url('client/index.php');
    return billing_safe_next((string)($_SERVER['REQUEST_URI'] ?? ''), $fallback);
}

/* ------------------------------------------------------------------
 * THE GATE
 *
 * Call this before accepting money. It stops the request when the
 * details are incomplete and points the client at the form, carrying
 * where they were so they land back on the payment, not the dashboard.
 * ------------------------------------------------------------------ */
function billing_require_complete(array $user, string $returnTo): void
{
    if (billing_complete($user)) {
        return;
    }

    $missing = billing_missing($user);
    if ($missing !== []) {
        $count = count($missing);
        $first = (string)$missing[0]['label'];
        $rest = $count > 1 ? ' and ' . ($count - 1) . ' more' : '';
        set_errors([
            'Before we can take this payment we need a few details about you. Still needed: ' . $first . $rest . '.',
        ]);
    } else {
        set_errors(['The payment details columns are missing on this install. Our team has been notified.']);
    }

    redirect(app_url('client/billing.php?next=' . urlencode($returnTo)));
}

/* ------------------------------------------------------------------
 * The form
 *
 * Rendered from billing_requirements(), so adding a field above adds
 * it here automatically. Input names are prefixed `bill_` so the form
 * can share a page with the checkout form without the two colliding
 * in the old-input store.
 * ------------------------------------------------------------------ */
function billing_form_html(array $user, array $errors = [], string $submitLabel = 'Save my payment details', string $next = '', string $intro = ''): void
{
    if (!billing_schema_ready()) {
        billing_schema_notice_html();
        return;
    }

    $groups = [
        'contact' => ['How we reach you', 'We use these to confirm your payment and to send your paperwork.'],
        'identity' => ['Who is paying', 'This is the identity your invoice and receipt will be raised to.'],
        'location' => ['Where you are', 'Used for delivery, for your contract, and to work out how to take your payment.'],
    ];
    $requirements = billing_requirements($user);
    // A terms error means the tick is outstanding right now — either the
    // first time, or because the client just changed a field that reaches
    // the invoice. The box has to come back in that case, or the form would
    // demand a tick it does not let anyone give.
    $termsDone = billing_terms_accepted($user) && !isset($errors['terms']);
    $progress = billing_progress($user);
    ?>
    <?php if ($intro !== ''): ?>
      <p class="billing-intro"><?= e($intro) ?></p>
    <?php endif; ?>

    <form method="post" class="billing-form" novalidate autocomplete="on" data-billing-form>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="billing_update">
      <input type="hidden" name="next" value="<?= e($next) ?>">

      <div class="billing-meter" data-billing-meter>
        <div class="bm-top">
          <span class="bm-label">Your payment details</span>
          <span class="bm-count"><b data-billing-done><?= (int)$progress['done'] ?></b> of <?= (int)$progress['total'] ?> complete</span>
        </div>
        <div class="bm-track"><span class="bm-fill" data-billing-fill style="width: <?= (int)$progress['percent'] ?>%"></span></div>
      </div>

      <?php if ($errors): ?>
        <div class="alert billing-summary-errors" role="alert">
          <b><?= icon('flag') ?> <?= count($errors) === 1 ? 'One thing to fix' : count($errors) . ' things to fix' ?></b>
          <p>We could not save your details yet. <?= count($errors) === 1 ? 'Fix the field' : 'Fix the fields' ?> marked below and try again — everything you have typed is kept.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($groups as $group => [$heading, $blurb]): ?>
        <?php $items = array_values(array_filter($requirements, static fn ($i) => $i['group'] === $group)); ?>
        <?php if (!$items) { continue; } ?>
        <fieldset class="billing-group" data-billing-group="<?= e($group) ?>">
          <legend><?= e($heading) ?></legend>
          <p class="bg-blurb"><?= e($blurb) ?></p>

          <?php foreach ($items as $item): ?>
            <?php
            $key = $item['key'];
            $id = 'bill_' . $key;
            $name = 'bill_' . $key;
            // old() escapes what it returns, so $value is printed raw
            // below. Wrapping it in e() again would turn a legitimate
            // "&" in a company name into "&amp;amp;".
            $value = old($name, (string)$item['value']);
            $err = $errors[$key] ?? '';
            $isBusinessField = !empty($item['business_only']);
            ?>
            <?php if ($item['type'] === 'choice'): ?>
              <div class="field" data-billing-choice data-billing-field="payer_type" data-required="1">
                <label for="<?= e($id) ?>"><?= e($item['label']) ?> <span class="req">*</span></label>
                <div class="choice-row">
                  <label class="choice" for="<?= e($id) ?>_business">
                    <input type="radio" id="<?= e($id) ?>_business" name="<?= e($name) ?>" value="business" <?= $value === 'business' ? 'checked' : '' ?>>
                    <span class="choice-box"><b>A registered business</b><small>Company, school, NGO, church or sole trader with a TIN.</small></span>
                  </label>
                  <label class="choice" for="<?= e($id) ?>_individual">
                    <input type="radio" id="<?= e($id) ?>_individual" name="<?= e($name) ?>" value="individual" <?= $value === 'individual' ? 'checked' : '' ?>>
                    <span class="choice-box"><b>Just me</b><small>No registered business — I am paying for myself.</small></span>
                  </label>
                </div>
                <div class="form-note"><?= e($item['why']) ?></div>
              </div>
            <?php elseif ($item['type'] === 'select'): ?>
              <div class="field<?= $isBusinessField ? ' business-only' : '' ?>"<?= $isBusinessField ? ' data-business-only' : '' ?>>
                <label for="<?= e($id) ?>"><?= e($item['label']) ?> <?= $item['required'] ? '<span class="req">*</span>' : '' ?></label>
                <select class="select" id="<?= e($id) ?>" name="<?= e($name) ?>" <?= $item['required'] ? 'required' : '' ?> data-billing-field="<?= e($key) ?>" data-required="<?= $item['required'] ? '1' : '0' ?>">
                  <?php foreach ($item['options'] as $opt): ?>
                    <option value="<?= e($opt) ?>" <?= $value === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if ($err): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
              </div>
            <?php else: ?>
              <div class="field<?= $isBusinessField ? ' business-only' : '' ?><?= $err ? ' has-error' : '' ?>"
                   <?= $isBusinessField ? 'data-business-only' : '' ?>
                   data-billing-item="<?= e($key) ?>">
                <label for="<?= e($id) ?>"><?= e($item['label']) ?> <?= $item['required'] ? '<span class="req">*</span>' : '<span class="opt">optional</span>' ?></label>
                <input class="input"
                       id="<?= e($id) ?>"
                       name="<?= e($name) ?>"
                       type="<?= e($item['type']) ?>"
                       value="<?= $value ?>"
                       maxlength="<?= (int)($item['max'] ?? 190) ?>"
                       <?= $item['required'] ? 'required' : '' ?>
                       <?= isset($item['placeholder']) ? 'placeholder="' . e($item['placeholder']) . '"' : '' ?>
                       <?= isset($item['autocomplete']) ? 'autocomplete="' . e($item['autocomplete']) . '"' : 'autocomplete="off"' ?>
                       data-billing-field="<?= e($key) ?>"
                       data-required="<?= $item['required'] ? '1' : '0' ?>"
                       <?= $item['type'] === 'tel' ? 'data-billing-phone="1"' : '' ?>>
                <?php if ($err): ?><div class="field-error"><?= e($err) ?></div><?php endif; ?>
                <?php if (!empty($item['hint'])): ?><div class="form-note" data-billing-hint="<?= e($key) ?>"><?= e($item['hint']) ?></div><?php endif; ?>
                <?php if ($item['type'] === 'tel'): ?>
                  <div class="phone-echo" data-billing-phone-echo aria-live="polite"></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </fieldset>
      <?php endforeach; ?>

      <fieldset class="billing-group billing-terms">
        <legend>Confirm and accept</legend>
        <?php if ($termsDone): ?>
          <div class="terms-done" data-billing-terms-done>
            <span class="td-icon"><?= icon('check') ?></span>
            <div>
              <b>Payment terms accepted<?= billing_terms_date($user) !== '' ? ' on ' . e(billing_terms_date($user)) : '' ?></b>
              <small>You only confirm these details again if you change them.</small>
            </div>
          </div>
        <?php endif; ?>
        <?php if (!$termsDone): ?>
          <label class="terms-tick<?= isset($errors['terms']) ? ' has-error' : '' ?>" for="bill_terms_agree">
            <input type="checkbox" id="bill_terms_agree" name="bill_terms_agree" value="1" data-billing-field="terms" data-required="1" <?= !empty($_POST['bill_terms_agree']) ? 'checked' : '' ?>>
            <span>
              <b>I confirm these details are correct and may be used on my invoice and receipt.</b>
              <small>If anything is wrong, tell us and we will fix the invoice before it is paid. Read the <a href="<?= app_url('terms.php') ?>" target="_blank" rel="noopener">Terms &amp; Conditions</a>.</small>
            </span>
          </label>
          <?php if (isset($errors['terms'])): ?><div class="field-error"><?= e($errors['terms']) ?></div><?php endif; ?>
        <?php endif; ?>
        <div class="terms-privacy"><?= icon('shield') ?> We store these details on your account only, to invoice you and reach you about this project. We never sell or share them.</div>
      </fieldset>

      <div class="billing-actions">
        <button class="btn btn-primary btn-block" type="submit"><?= icon('check') ?> <?= e($submitLabel) ?></button>
        <p class="small muted center mt-2 mb-0"><?= icon('lock') ?> Saved to your account. You will not be asked again unless you change it.</p>
      </div>
    </form>
    <?php
}

/* ------------------------------------------------------------------
 * When the columns are missing. An administrator note, not a client
 * one — a client cannot fix a missing migration.
 * ------------------------------------------------------------------ */
function billing_schema_notice_html(): void
{
    ?>
    <div class="alert alert-error billing-schema-note">
      <b><?= icon('error') ?> Payment details cannot be saved on this install.</b>
      <p>The <code>users</code> table is missing the payment details columns, so nobody can pay until it is updated. Import <code>database/upgrade.sql</code> into this database — it is safe to run more than once and changes nothing else.</p>
    </div>
    <?php
}

/* ------------------------------------------------------------------
 * The lock notice.
 *
 * Shown in place of a payment form when the gate is closed. It leads
 * with what is still missing rather than a refusal, so the client
 * always knows the payment is a minute away, not a dead end.
 * ------------------------------------------------------------------ */
function billing_lock_notice_html(array $user, string $returnTo, string $lead = ''): void
{
    if (!billing_schema_ready()) {
        billing_schema_notice_html();
        return;
    }
    $missing = billing_missing($user);
    $progress = billing_progress($user);
    $lead = $lead !== '' ? $lead : 'We need a few details about you before we can take this payment. It takes about a minute, and you only do it once.';
    ?>
    <section class="billing-lock" data-billing-lock>
      <div class="bl-head">
        <span class="bl-icon"><?= icon('lock') ?></span>
        <div>
          <h3>Confirm your payment details first</h3>
          <p><?= e($lead) ?></p>
        </div>
      </div>

      <div class="billing-meter">
        <div class="bm-top">
          <span class="bm-label">Your payment details</span>
          <span class="bm-count"><b><?= (int)$progress['done'] ?></b> of <?= (int)$progress['total'] ?> complete</span>
        </div>
        <div class="bm-track"><span class="bm-fill" style="width: <?= (int)$progress['percent'] ?>%"></span></div>
      </div>

      <?php if ($missing): ?>
        <p class="bl-sub">Still needed:</p>
        <ul class="bl-list">
          <?php foreach ($missing as $item): ?>
            <li><span class="bl-tick"><?= icon('next') ?></span><span><b><?= e($item['label']) ?></b><?= $item['why'] !== '' ? ' <small>' . e($item['why']) . '</small>' : '' ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="bl-sub">Everything is filled in. One last save to confirm it.</p>
      <?php endif; ?>

      <a class="btn btn-primary btn-block" href="<?= e(app_url('client/billing.php?next=' . urlencode($returnTo))) ?>">
        <?= icon('edit') ?> Complete my details<?= $missing ? ' (' . count($missing) . ' left)' : '' ?>
      </a>
      <p class="small muted center mt-2 mb-0"><?= icon('lock') ?> Your payment is not lost — you will come straight back here.</p>
    </section>
    <?php
}

/* ------------------------------------------------------------------
 * A one-line receipt of who the invoice will be raised to, shown
 * beside a payment form so the client can sanity-check it without
 * leaving the page.
 * ------------------------------------------------------------------ */
function billing_summary_html(array $user): void
{
    $lines = [];
    $lines[] = trim((string)($user['full_name'] ?? ''));
    if (billing_is_business($user)) {
        $company = trim((string)($user['company'] ?? ''));
        if ($company !== '') {
            $lines[] = $company;
        }
        $taxId = trim((string)($user['tax_id'] ?? ''));
        if ($taxId !== '') {
            $lines[] = 'TIN ' . $taxId;
        }
    }
    $locality = trim((string)($user['city'] ?? ''));
    $country = trim((string)($user['country'] ?? ''));
    if ($locality !== '' || $country !== '') {
        $lines[] = trim($locality . ($locality !== '' && $country !== '' ? ', ' : '') . $country);
    }
    $lines = array_values(array_filter($lines));
    if (!$lines) {
        return;
    }
    ?>
    <div class="billing-summary">
      <span class="bs-icon"><?= icon('receipt') ?></span>
      <div>
        <b>Your invoice will be raised to</b>
        <small><?= e(implode(' · ', $lines)) ?></small>
      </div>
      <a href="<?= e(app_url('client/billing.php')) ?>" class="bs-edit"><?= icon('edit') ?> Edit</a>
    </div>
    <?php
}

/* ------------------------------------------------------------------
 * The date the terms were accepted, for display.
 * ------------------------------------------------------------------ */
function billing_terms_date(array $user): string
{
    $raw = trim((string)($user['billing_terms_at'] ?? ''));
    if ($raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    return $ts === false ? '' : date('d M Y', $ts);
}

/* ------------------------------------------------------------------
 * Handle a submitted billing form.
 *
 * Reads and validates $_POST, and saves it when it is clean. Returns
 * the field-keyed errors on failure so the caller can redraw the form
 * with the same values still in the boxes, and redirects to the page
 * the client came from on success.
 *
 * Errors are returned, not thrown and not put in the session: the
 * form owns them, so there is no second reader waiting to consume
 * them first and swallow the lot.
 * ------------------------------------------------------------------ */
function billing_process_post(PDO $pdo, array $user): array
{
    [$errors, $clean] = billing_validate($user, billing_post_fields($_POST));
    if ($errors) {
        keep_old(array_keys($_POST));
        return [$errors, []];
    }

    try {
        billing_save($pdo, $user, $clean);
    } catch (Throwable $e) {
        log_error('billing: ' . $e->getMessage());
        keep_old(array_keys($_POST));
        return [['_form' => 'We could not save your payment details just now. Please try again in a moment.'], []];
    }

    clear_old();
    audit('billing_details_updated', 'users', (int)$user['id'], 'Client completed their payment details');
    return [[], $clean];
}

/* ------------------------------------------------------------------
 * Turn the `bill_`-prefixed POST back into the flat shape
 * billing_validate() expects, so callers do not have to know the
 * prefix exists. The terms tick is mapped across here too, because it
 * is the one field whose validator name has no column behind it.
 * ------------------------------------------------------------------ */
function billing_post_fields(array $post): array
{
    $out = [];
    foreach ($post as $key => $value) {
        if (is_string($key) && str_starts_with($key, 'bill_')) {
            $out[substr($key, 5)] = $value;
        }
    }
    return $out;
}
