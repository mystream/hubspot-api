<?php

#[Attribute(Attribute::TARGET_METHOD)]
class HubspotEndpoint {
    public function __construct(
        public string $category,
        public string $operation,
        public string $description = ''
    ) {}
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class HubspotParam {
    public function __construct(
        public string $type,
        public string $description = '',
        public bool   $required    = true
    ) {}
}

class hubspot_service {

	public static function getBaseUrl(): string;
	public static function getAccessToken(): string;

	private static function get(
	    string $endpoint,
	    array  $body    = [],
	    array  $query   = [],
	    array  $headers = []
	) {
		return self::request( 'GET', $endpoint, $body, $query, $headers );
	}

	private static function post(
	    string $endpoint,
	    array  $body    = [],
	    array  $query   = [],
	    array  $headers = []
	) {
		return self::request( 'POST', $endpoint, $body, $query, $headers );
	}

	private static function patch(
	    string $endpoint,
	    array  $body    = [],
	    array  $query   = [],
	    array  $headers = []
	) {
		return self::request( 'PATCH', $endpoint, $body, $query, $headers );
	}

	private static function put(
	    string $endpoint,
	    array  $body    = [],
	    array  $query   = [],
	    array  $headers = []
	) {
		return self::request( 'PUT', $endpoint, $body, $query, $headers );
	}

	private static function request(
	    string $method,
	    string $endpoint,
	    array  $body    = [],
	    array  $query   = [],
	    array  $headers = []
	): array {
	    $baseUrl = getenv('HUBSPOT_BASE_URL') ?: 'https://api.hubapi.com';
	    $token   = getenv('HUBSPOT_ACCESS_TOKEN');
	
	    if (!$token) {
	        return [
	            'status'  => 500,
	            'error'   => 'Missing HubSpot access token in environment.',
	            'payload' => null
	        ];
	    }
	
	    $url = rtrim( $baseUrl, '/' ) . '/' . ltrim( $endpoint, '/' );
	    if ( !empty( $query ) ) {
	        $url .= '?' . http_build_query( $query );
	    }
	
	    $defaultHeaders = [
	        'Authorization: Bearer ' . $token,
	        'Content-Type: application/json',
	        'Accept: application/json'
	    ];
	    $finalHeaders = array_merge( $defaultHeaders, $headers );
	
	    $ch = curl_init();
	    curl_setopt_array($ch, [
	        CURLOPT_URL				=> $url,
	        CURLOPT_RETURNTRANSFER	=> true,
	        CURLOPT_CUSTOMREQUEST	=> strtoupper( $method ),
	        CURLOPT_HTTPHEADER		=> $finalHeaders,
	        CURLOPT_TIMEOUT			=> 10,
	    ]);
	
	    if ( in_array( strtoupper( $method ), [ 'POST', 'PUT', 'PATCH' ] ) ) {
	        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
	    }
	
	    $response  = curl_exec( $ch );
	    $status    = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	    $curlError = curl_error( $ch );
	    curl_close( $ch );
	
	    if ( $curlError ) {
	        return [
	            'status'	=> 500,
	            'error'		=> 'cURL error: ' . $curlError,
	            'payload'	=> null
	        ];
	    }
	
	    $decoded	= json_decode($response, true);
	    $error		= ($status >= 400) ? ($decoded['message'] ?? 'HTTP error') : null;
	
	    return [
	        'status'	=> $status,
	        'error'		=> $error,
	        'payload'	=> $decoded
	    ];
	}
	
	// Owner Information
	#[HubSpotMethod(
		name		: "ownerSearch",
		category	: "Owner Information",
		description	: "Owner Information"
	)]
	public static function ownerSearch(): array {
		$response = self::get( "/owners" );
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "ownerLoad",
		category	: "Owner Information",
		description	: "Owner Information"
	)]
	public static function ownerLoad(
		#[HubSpotArgument(
			name		: "id",
			type		: "string",
			validation	: "required"
		)]
		string $id
	): ?array {
		$response = self::get( "/owners/" . $id, query: [
			'id' => $id
		]);
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	// Contact Information
	#[HubSpotMethod(
		name		: "contactLoadById",
		category	: "Contact Information",
		description	: "Contact Information"
	)]
	public static function contactLoadById(
		#[HubSpotArgument(
			name		: "id",
			type		: "string",
			validation	: "required"
		)]
		string $id
	): ?array {
		$response = self::get( "/contacts/" . $id, query: [
			'id' => $id
		]);
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "contactSearch",
		category	: "Contact Information",
		description	: "Contact Information"
	)]
	public static function contactSearch(
		#[HubSpotArgument(
			name: "filters",
			type: "array"
		)]
		array $filters	= [],

		#[HubSpotArgument(
			name: "limit",
			type: "int"
		)]
		int $limit		= 20,

		#[HubSpotArgument(
			name: "offset",
			type: "int"
		)]
		int $offset		= 0
	): array {
		$response = self::get( "/contacts/search", body: [
			'filters' => $filters,
			'limit'   => $limit,
			'offset'  => $offset
		]);
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "contactUpdate",
		category	: "Contact Information",
		description	: "Contact Information"
	)]
	public static function contactUpdate(
		#[HubSpotArgument(
			name		: "data",
			type		: "array",
			validation	: "required"
		)]
		array $data
	): ?array {
		$id = $data[ 'id' ] ?? null;
		if ( !$id ) {
			return [
				'status'  => 400,
				'error'   => 'Missing contact ID in data payload.',
				'payload' => []
			];
		}

		$response = self::patch( "/crm/v3/objects/contacts/" . $id, body: [
			'properties' => $data
		]);
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "contactDelete",
		category	: "Contact Information",
		description	: "Contact Information"
	)]
	public static function contactDelete(
		#[HubSpotArgument(
			name		: "email",
			type		: "string",
			validation	: "email|required"
		)]
		string $email
	): bool {
		$lookup	= self::contactLoadByEmail( $email );
		$id		= $lookup[ 'payload' ][ 'id' ] ?? null;

		if ( !$id ) {
			return false;
		}

		$response = self::delete( "/crm/v3/objects/contacts/" . $id );
		return $response[ 'status' ] >= 200 && $response[ 'status' ] < 300;
	}

	#[HubSpotMethod(
		name		: "contactLoadByEmail",
		category	: "Contact Information",
		description	: "Contact Information"
	)]
	public static function contactLoadByEmail(
		#[HubSpotArgument(
			name		: "email",
			type		: "string",
			validation	: "email|required"
		)]
		string $email
	): ?array {
		$response = self::post( "/crm/v3/objects/contacts/search", body: [
			'filterGroups' => [[
				'filters' => [[
					'propertyName'	=> 'email',
					'operator'		=> 'EQ',
					'value'			=> $email
				]]
			]],
			'limit' => 1
		]);

		$results = $response[ 'payload' ][ 'results' ] ?? [];
		return [
			'status'	=> $response[ 'status' ] ?? 500,
			'error'		=> $response[ 'error' ]  ?? null,
			'payload'	=> $results[ 0 ] ?? null
		];
	}

	// Company Information
	#[HubSpotMethod(
		name		: "companyLoadById",
		category	: "Company Information",
		description	: "Company Information"
	)]
	public static function companyLoadById(
		#[HubSpotArgument(
			name		: "id",
			type		: "string",
			validation	: "required"
		)]
		string $id
	): ?array {
		$response = self::get( "/crm/v3/objects/companies/" . $id );
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "companyLoadByDomain",
		category	: "Company Information",
		description	: "Company Information"
	)]
	public static function companyLoadByDomain(
		#[HubSpotArgument(
			name		: "domain",
			type		: "string",
			validation	: "required"
		)]
		string $domain
	): ?array {
		$response = self::post( "/crm/v3/objects/companies/search", body: [
			'filterGroups' => [[
				'filters' => [[
					'propertyName'	=> 'domain',
					'operator'		=> 'EQ',
					'value'			=> $domain
				]]
			]],
			'limit' => 1
		]);

		$results = $response[ 'payload' ][ 'results' ] ?? [];
		return [
			'status'  => $response[ 'status' ] ?? 500,
			'error'   => $response[ 'error' ]  ?? null,
			'payload' => $results[0] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "companySearch",
		category	: "Company Information",
		description	: "Company Information"
	)]
	public static function companySearch(
		#[HubSpotArgument(
			name		: "filters",
			type		: "array",
			validation	: "optional"
		)]
		array $filters = [],

		#[HubSpotArgument(
			name		: "limit",
			type		: "int",
			validation	: "min:1"
		)]
		int $limit     = 20,

		#[HubSpotArgument(
			name		: "offset",
			type		: "int",
			validation	: "min:0"
		)]
		int $offset    = 0
	): array {
		$response = self::post( "/crm/v3/objects/companies/search", body: [
			'filterGroups'	=> $filters,
			'limit'			=> $limit,
			'after'			=> $offset
		]);

		return [
			'status'  => $response[ 'status' ] ?? 500,
			'error'   => $response[ 'error' ]  ?? null,
			'payload' => $response[ 'payload' ][ 'results' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "companyUpdate",
		category	: "Company Information",
		description	: "Company Information"
	)]
	public static function companyUpdate(
		#[HubSpotArgument(
			name		: "data",
			type		: "array",
			validation	: "required"
		)]
		array $data
	): ?array {
		$id = $data[ 'id' ] ?? null;
		if ( !$id ) {
			return [
				'status'	=> 400,
				'error'		=> 'Missing required company ID in update data.',
				'payload'	=> null
			];
		}

		$response = self::patch( "/crm/v3/objects/companies/" . $id, body: [
			'properties' => $data
		]);

		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
 		];
	}

	#[HubSpotMethod(
		name		: "companyDelete",
		category	: "Company Information",
		description	: "Company Information"
	)]
	public static function companyDelete(
		#[HubSpotArgument(
			name		: "domain",
			type		: "string",
			validation	: "required"
		)]
		string $domain
	): bool {
		$search  = self::companyLoadByName( $domain );
		$company = $search[ 'payload' ] ?? null;

		if ( !$company || empty( $company[ 'id' ] ) ) {
			return false;
		}

		$response = self::delete( "/crm/v3/objects/companies/" . $company[ 'id' ] );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}
	
	// Deal Information
	#[HubSpotMethod(
		name		: "dealLoadById",
		category	: "Deal Information",
		description	: "Deal Information"
	)]
	public static function dealLoadById(
		#[HubSpotArgument(
			name		: "id",
			type		: "string",
			validation	: "required"
		)]
		string $id
	): ?array {
		$response = self::get( "/crm/v3/objects/deals/" . $id );
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "dealLoadByName",
		category	: "Deal Information",
		description	: "Deal Information"
	)]
	public static function dealLoadByName(
		#[HubSpotArgument(
			name		: "dealName",
			type		: "string",
			validation	: "required"
		)]
		string $dealName
	): ?array {
		$filters = [[
			'filters' => [[
				'propertyName'	=> 'dealname',
				'operator'		=> 'EQ',
				'value'			=> $dealName
			]]
		]];

		$search = self::dealSearch(filters: $filters, limit: 1);
		return [
			'status'	=> $search[ 'status' ],
			'error'		=> $search[ 'error' ],
			'payload'	=> $search[ 'payload' ][0] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "dealSearch",
		category	: "Deal Information",
		description	: "Deal Information"
	)]
	public static function dealSearch(
		#[HubSpotArgument(
			name		: "filters",
			type		: "array",
			validation	: "optional"
		)]
		array $filters	= [],

		#[HubSpotArgument(
			name		: "limit",
			type		: "int",
			validation	: "min:1"
		)]
		int $limit		= 20,

		#[HubSpotArgument(
			name		: "offset",
			type		: "int",
			validation	: "min:0"
		)]
		int $offset		= 0
	): array {
		$response = self::post( "/crm/v3/objects/deals/search", body: [
			'filterGroups'	=> $filters,
			'limit'			=> $limit,
			'after'			=> $offset
		]);
		return [
			'status'	=> $response[ 'status' ] ?? 500,
			'error'		=> $response[ 'error' ]  ?? null,
			'payload'	=> $response[ 'payload' ][ 'results' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "dealUpdate",
		category	: "Deal Information",
		description	: "Deal Information"
	)]
	public static function dealUpdate(
		#[HubSpotArgument(
			name		: "data",
			type		: "array",
			validation	: "required"
		)]
		array $data
	): ?array {
		$id = $data[ 'id' ] ?? null;
		if ( !$id ) {
			return [
				'status'	=> 400,
				'error'		=> 'Missing deal ID in update payload.',
				'payload'	=> null
			];
		}

		$response = self::patch( "/crm/v3/objects/deals/" . $id, body: [
			'properties' => $data
		]);
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "dealDelete",
		category	: "Deal Information",
		description	: "Deal Information"
	)]
	public static function dealDelete(
		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "required"
		)]
		string $dealId
	): bool {
		$response = self::delete( "/crm/v3/objects/deals/" . $dealId );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}

	// Deal Pipeline
	#[HubSpotMethod(
		name		: "dealPipelineSearch",
		category	: "Deal Pipeline",
		description	: "Deal Pipeline"
	)]
	public static function dealPipelineSearch(): array {
		$response = self::get( "/crm/v3/pipelines/deals" );
		return [
			'status'	=> $response[ 'status' ] ?? 500,
			'error'		=> $response[ 'error' ]  ?? null,
			'payload'	=> $response[ 'payload' ][ 'results' ] ?? [],
		];
	}

	#[HubSpotMethod(
		name		: "dealPipelineLoad",
		category	: "Deal Pipeline",
		description	: "Deal Pipeline"
	)]
	public static function dealPipelineLoad(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required"
		)]
		string $pipelineId
	): array {
		$response = self::get( "/crm/v3/pipelines/deals/" . $pipelineId );
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "dealPipelineCreate",
		category	: "Deal Pipeline",
		description	: "Deal Pipeline"
	)]
	public static function dealPipelineCreate(
		#[HubSpotArgument(
			name		: "data",
			type		: "array",
			validation	: "required"
		)]
		array $data
	): array {
		$response = self::post( "/crm/v3/pipelines/deals", body: $data );
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "dealPipelineUpdate",
		category	: "Deal Pipeline",
		description	: "Deal Pipeline"
	)]
	public static function dealPipelineUpdate(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required"
		)]
		string $pipelineId,

		#[HubSpotArgument(
			name		: "data",
			type		: "array",
			validation	: "required"
		)]
		array $data
	): array {
		$response = self::put( "/crm/v3/pipelines/deals/" . $pipelineId, body: $data );
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "dealPipelineDelete",
		category	: "Deal Pipeline",
		description	: "Deal Pipeline"
	)]
	public static function dealPipelineDelete(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required"
		)]
		string $pipelineId
	): array {
		$response = self::delete( "/crm/v3/pipelines/deals/" . $pipelineId );
		return [
			'status'	=> $response[ 'status' ]  ?? 500,
			'error'		=> $response[ 'error' ]   ?? null,
			'payload'	=> $response[ 'payload' ] ?? null
		];
	}

	// Deal Stage
	#[HubSpotMethod(
		name		: "dealPipelineStageSearch",
		category	: "Deal Pipeline",
		description	: "Load all stages for a deal pipeline"
	)]
	public static function dealPipelineStageSearch(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required",
			validate	: 'uuid'
		)]
		string $pipelineId
	): array {
		return self::get( "/crm/v3/pipelines/deals/" . $pipelineId . "/stages" );
	}

	#[HubSpotMethod(
		name		: "dealPipelineStageLoad",
		category	: "Deal Pipeline",
		description	: "Load a specific deal stage"
	)]
	public static function dealPipelineStageLoad(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $pipelineId,

		#[HubSpotArgument(
			name		: "stageId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $stageId
	): array {
		return self::get( "/crm/v3/pipelines/deals/" . $pipelineId . "/stages/" . $stageId );
	}

	#[HubSpotMethod(
		name		: "dealPipelineStageCreate",
		category	: "Deal Pipeline",
		description	: "Create a new stage in a deal pipeline"
	)]
	public static function dealPipelineStageCreate(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $pipelineId,

		#[HubSpotArgument(
			name		: "data",
			type		: "array"
		)]
		array $data
	): array {
		return self::post( "/crm/v3/pipelines/deals/" . $pipelineId . "/stages", $data );
	}

	#[HubSpotMethod(
		name		: "dealPipelineStageUpdate",
		category	: "Deal Pipeline",
		description	: "Update an existing deal stage"
	)]
	public static function dealPipelineStageUpdate(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $pipelineId,

		#[HubSpotArgument(
			name		: "stageId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $stageId,

		#[HubSpotArgument(
			name		: "data",
			type		: "array"
		)]
		array $data
	): array {
		return self::patch( "/crm/v3/pipelines/deals/" . $pipelineId . "/stages/" . $stageId, $data );
	}

	#[HubSpotMethod(
		name		: "dealPipelineStageDelete",
		category	: "Deal Pipeline",
		description	: "Delete a deal stage"
	)]
	public static function dealPipelineStageDelete(
		#[HubSpotArgument(
			name		: "pipelineId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $pipelineId,

		#[HubSpotArgument(
			name		: "stageId",
			type		: "string",
			validation	: "required",
			validate	: "uuid"
		)]
		string $stageId
	): array {
		return self::delete( "/crm/v3/pipelines/deals/" . $pipelineId . "/stages/" . $stageId );
	}

	// Linking Companies, Contacts and Deals
	#[HubSpotMethod(
		name		: "associateContactWithCompany",
		category	: "Associations",
		description	: "Associate a contact with a company."
	)]
	public static function associateContactWithCompany(
		#[HubSpotArgument(
			name		: "contactId",
			type		: "string",
			validation	: "uuid",
			description	: "The contact ID."
		)]
		string $contactId,

		#[HubSpotArgument(
			name		: "companyId",
			type		: "string",
			validation	: "uuid",
			description	: "The company ID."
		)]
		string $companyId
	): bool {
		$response = self::put( "/crm/v3/objects/contacts/" . $contactId . "/associations/companies/" . $companyId . "/contact_to_company" );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}

	#[HubSpotMethod(
		name		: "dissociateContactFromCompany",
		category	: "Associations",
		description	: "Remove an association between a contact and a company."
	)]
	public static function dissociateContactFromCompany(
		#[HubSpotArgument(
			name		: "contactId",
			type		: "string",
			validation	: "uuid",
			description	: "The contact ID."
		)]
		string $contactId,

		#[HubSpotArgument(
			name		: "companyId",
			type		: "string",
			validation	: "uuid",
			description	: "The company ID."
		)]
		string $companyId
	): bool {
		$response = self::delete( "/crm/v3/objects/contacts/" . $contactId . "/associations/companies/" . $companyId . "/contact_to_company" );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}
	
	#[HubSpotMethod(
		name		: "associateContactWithDeal",
		category	: "Associations",
		description	: "Associate a contact with a deal."
	)]
	public static function associateContactWithDeal(
		#[HubSpotArgument(
			name		: "contactId",
			type		: "string",
			validation	: "uuid",
			description	: "The contact ID."
		)]
		string $contactId,

		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "uuid",
			description	: "The deal ID."
		)]
		string $dealId
	): bool {
		$response = self::put( "/crm/v3/objects/contacts/" . $contactId . "/associations/deals/" . $dealId . "/contact_to_deal" );
		return ( $response[ 'status' ] ?? 500) === 204;
	}

	#[HubSpotMethod(
		name		: "dissociateContactFromDeal",
		category	: "Associations",
		description	: "Remove an association between a contact and a deal."
	)]
	public static function dissociateContactFromDeal(
		#[HubSpotArgument(
			name		: "contactId",
			type		: "string",
			validation	: "uuid",
			description	: "The contact ID."
		)]
		string $contactId,

		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "uuid",
			description	: "The deal ID."
		)]
		string $dealId
	): bool {
		$response = self::delete( "/crm/v3/objects/contacts/" . $contactId . "/associations/deals/" . $dealId . "/contact_to_deal" );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}
	
	#[HubSpotMethod(
		name		: "associateDealWithCompany",
		category	: "Associations",
		description	: "Associate a deal with a company."
	)]
	public static function associateDealWithCompany(
		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "uuid",
			description	: "The deal ID."
		)]
		string $dealId,

		#[HubSpotArgument(
			name		: "companyId",
			type		: "string",
			validation	: "uuid",
			description	: "The company ID."
		)]
		string $companyId
	): bool {
		$response = self::put( "/crm/v3/objects/deals/" . $dealId . "/associations/companies/" . $companyId . "/deal_to_company" );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}

	#[HubSpotMethod(
		name		: "dissociateDealFromCompany",
		category	: "Associations",
		description	: "Remove an association between a deal and a company."
	)]
	public static function dissociateDealFromCompany(
		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "uuid",
			description	: "The deal ID."
		)]
		string $dealId,

		#[HubSpotArgument(
			name		: "companyId",
			type		: "string",
			validation	: "uuid",
			description	: "The company ID."
		)]
		string $companyId
	): bool {
		$response = self::delete( "/crm/v3/objects/deals/" . $dealId . "/associations/companies/" .$companyId . "/deal_to_company" );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}
	
	#[HubSpotMethod(
		name		: "associateDealWithContact",
		category	: "Associations",
		description	: "Associate a deal with a contact."
	)]
	public static function associateDealWithContact(
		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "uuid",
			description	: "The deal ID."
		)]
		string $dealId,

		#[HubSpotArgument(
			name		: "contactId",
			type		: "string",
			validation	: "uuid",
			description	: "The contact ID."
		)]
		string $contactId
	): array {
		$response = self::put( "/crm/v3/objects/deals/" . $dealId . "/associations/contacts/" . $contactId . "/deal_to_contact" );
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	#[HubSpotMethod(
		name		: "dissociateDealFromContact",
		category	: "Associations",
		description	: "Remove an association between a deal and a contact."
	)]
	public static function dissociateDealFromContact(
		#[HubSpotArgument(
			name		: "dealId",
			type		: "string",
			validation	: "uuid",
			description	: "The deal ID."
		)]
		string $dealId,

		#[HubSpotArgument(
			name		: "contactId",
			type		: "string",
			validation	: "uuid",
			description	: "The contact ID."
		)]
		string $contactId
	): array {
		$response = self::delete( "/crm/v3/objects/deals/" . $dealId . "/associations/contacts/" . $contactId . "/deal_to_contact" );
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	// Engagements
	#[HubSpotMethod(
		name		: "engagementCreate",
		category	: "Engagements",
		description	: "Create a new engagement record."
	)]
	public static function engagementCreate(
		#[HubSpotArgument(
			name		: "data",
			type		: "array",
			validation	: "required",
			description	: "Engagement data including type, associations, metadata."
		)]
		array $data
	): ?array {
		$response = self::post( '/crm/v3/objects/engagements', body: $data);
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "engagementDelete",
		category	: "Engagements",
		description	: "Delete an engagement by ID."
	)]
	public static function engagementDelete(
		#[HubSpotArgument(
			name		: "engagementId",
			type		: "string",
			validation	: "uuid",
			description	: "The ID of the engagement to delete."
		)]
		string $engagementId
	): bool {
		$response = self::delete( "/crm/v3/objects/engagements/" . $engagementId );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}

	#[HubSpotMethod(
		name		: "engagementSearch",
		category	: "Engagements",
		description	: "Search for engagements using filters."
	)]
	public static function engagementSearch(
		#[HubSpotArgument(
			name		: "filters",
			type		: "array",
			description	: "Search filters for engagement properties."
		)]
		array $filters = [],

		#[HubSpotArgument(
			name		: "limit",
			type		: "int",
			description	: "Maximum number of results to return."
		)]
		int $limit = 20,

		#[HubSpotArgument(
			name		: "offset",
			type		: "int",
			description	: "Pagination offset for results."
		)]
		int $offset = 0
	): array {
		$searchPayload = [
			'filterGroups'	=> [[ 'filters' => $filters ]],
			'limit'			=> $limit,
			'after'			=> $offset
		];

		$response = self::post( '/crm/v3/objects/engagements/search', body: $searchPayload );
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}

	// Lists
	#[HubSpotMethod(
		name		: "listLoad",
		category	: "Lists",
		description	: "Load a static or dynamic list by ID."
	)]
	public static function listLoad(
		#[HubSpotArgument(
			name		: "listId",
			type		: "string",
			validation	: "uuid",
			description	: "ID of the list."
		)]
		string $listId
	): ?array {
		$response = self::get( "/contacts/v1/lists/" . $listId );
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? null
		];
	}

	#[HubSpotMethod(
		name		: "listCreate",
		category	: "Lists",
		description	: "Create a new list."
	)]
	public static function listCreate(
		#[HubSpotArgument(
			name		: "listData",
			type		: "array",
			validation	: "required",
			description	: "List details including name, filters, and dynamic/static flag."
		)]
		array $listData
	): ?array {
		$response = self::post( "/contacts/v1/lists", body: $listData );
		return [
			'status'  => $response[ 'status' ]	?? 500,
			'error'   => $response[ 'error' ] 	?? null,
			'payload' => $response[ 'payload' ]	?? null
		];
	}

	#[HubSpotMethod(
		name		: "listUpdate",
		category	: "Lists",
		description	: "Update an existing list."
	)]
	public static function listUpdate(
		#[HubSpotArgument(
			name		: "listId",
			type		: "string",
			validation	: "uuid",
			description	: "ID of the list to update."
		)]
		string $listId,

		#[HubSpotArgument(
			name		: "listData",
			type		: "array",
			validation	: "required",
			description	: "Updated list details."
		)]
		array $listData
	): bool {
		$response = self::post( "/contacts/v1/lists/{$listId}", body: $listData );
		return ( $response[ 'status' ] ?? 500 ) < 300;
	}

	#[HubSpotMethod(
		name		: "listDelete",
		category	: "Lists",
		description	: "Delete a list by ID."
	)]
	public static function listDelete(
		#[HubSpotArgument(
			name		: "listId",
			type		: "string",
			validation	: "uuid",
			description	: "ID of the list to delete."
		)]
		string $listId
	): bool {
		$response = self::delete( "/contacts/v1/lists/" . $listId );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}

	#[HubSpotMethod(
		name		: "listContacts",
		category	: "Lists",
		description	: "Get contacts from a list."
	)]
	public static function listContacts(
		#[HubSpotArgument(
			name		: "listId",
			type		: "string",
			validation	: "uuid",
			description	: "ID of the list."
		)]
		string $listId,

		#[HubSpotArgument(
			name		: "limit",
			type		: "int",
			description	: "Number of contacts to return."
		)]
		int $limit = 100,

		#[HubSpotArgument(
			name		: "offset",
			type		: "int",
			description	: "Pagination offset."
		)]
		int $offset = 0
	): array {
		$response = self::get( "/contacts/v1/lists/" . $listId . "/contacts/all", query: [
			'count'		=> $limit,
			'vidOffset'	=> $offset
		]);
		return [
			'status'  => $response[ 'status' ]  ?? 500,
			'error'   => $response[ 'error' ]   ?? null,
			'payload' => $response[ 'payload' ] ?? []
		];
	}


	// Call
	#[HubSpotMethod(
		name		: "callCreate",
		category	: "Calls",
		description	: "Create a logged call engagement."
	)]
	public static function callCreate(
		#[HubSpotArgument(
			name		: "callData",
			type		: "array",
			validation	: "required",
			description	: "The engagement data for the call."
		)]
		array $callData
	): ?array {
		$response = self::post( '/engagements/v1/engagements', body: $callData );
		return [
			'status'  => $response[ 'status' ]	?? 500,
			'error'   => $response[ 'error' ] 	?? null,
			'payload' => $response[ 'payload' ]	?? null
		];
	}

	#[HubSpotMethod(
		name		: "callUpdate",
		category	: "Calls",
		description	: "Update a logged call engagement."
	)]
	public static function callUpdate(
		#[HubSpotArgument(
			name		: "callId",
			type		: "string",
			validation	: "uuid",
			description	: "The ID of the call to update."
		)]
		string $callId,

		#[HubSpotArgument(
			name		: "callData",
			type		: "array",
			validation	: "required",
			description	: "The updated data for the call."
		)]
		array $callData
	): bool {
		$response = self::patch( "/engagements/v1/engagements/" . $callId, body: $callData );
		return ( $response[ 'status' ] ?? 500 ) < 300;
	}
	#[HubSpotMethod(
		name		: "callDelete",
		category	: "Calls",
		description	: "Delete a logged call engagement."
	)]
	public static function callDelete(
		#[HubSpotArgument(
			name		: "callId",
			type		: "string",
			validation	: "uuid",
			description	: "The ID of the call to delete."
		)]
		string $callId
	): bool {
		$response = self::delete( "/engagements/v1/engagements/" . $callId );
		return ( $response[ 'status' ] ?? 500 ) === 204;
	}


	// Quotes
	public static function quoteCreate(array $quoteData): ?array;
	public static function quoteLoad(string $quoteId): ?array;
	public static function quoteUpdate(string $quoteId, array $quoteData): bool;
	public static function quoteDelete(string $quoteId): bool;
	
	// CRM Extensions Card
	public static function crmExtensionCardCreate(array $data): ?array;
	public static function crmExtensionCardUpdate(string $id, array $data): bool;
	public static function crmExtensionCardDelete(string $id): bool;
	
	// Custom Objects
	public static function customObjectCreate(string $objectType, array $data): ?array;
	public static function customObjectLoad(string $objectType, string $id): ?array;
	public static function customObjectDelete(string $objectType, string $id): bool;

	// Products
	public static function productSearch(array $filters = [], int $limit = 20, int $offset = 0): array;
	public static function productCreate(array $productData): array;
	public static function productLoad(string $productId): array;
	public static function productUpdate(array $data): ?array;
	public static function productDelete(string $id): bool;

	// Product Properties (schema)
	public static function productPropertiesLoad(): array;
	public static function productPropertyCreate(array $propertyData): array;
	public static function productPropertyUpdate(string $propertyName, array $propertyData): array;
	public static function productPropertyDelete(string $propertyName): array;

	// Product Folders
	public static function productFoldersLoad(): array;
	public static function productFolderCreate(array $folderData): array;
	public static function productFolderLoad(string $folderId): array;
	public static function productFolderUpdate(string $folderId, array $folderData): array;
	public static function productFolderDelete(string $folderId): array;

	// Line Items
	public static function lineItemCreate(array $lineItemData): ?array;
	public static function lineItemLoad(string $lineItemId): ?array;
	public static function lineItemUpdate(string $lineItemId, array $lineItemData): bool;
	public static function lineItemDelete(string $lineItemId): bool;
	
	// Form Definitions
	public static function formSearch(): array;
	public static function formSubmit(string $portalId, string $formGuid, array $fields, array $context = []): bool;
	public static function formLoad(string $formId): array;
	public static function formCreate(array $formData): array;
	public static function formUpdate(string $formId, array $formData): array;
	public static function formDelete(string $formId): array;
	public static function formSubmissionsLoad(string $formId, array $filters = []): array;

	// Form Performance
	public static function formPerformanceLoad(string $formId): array;

	// Form Fields (useful for form builder UIs)
	public static function formFieldsLoad(string $formId): array;
	
	public static function emailEvents(string $recipientEmail): array;

	// Marketing Emails
	public static function marketingEmailList(int $limit = 100, int $offset = 0): array;
	public static function marketingEmailLoad(string $emailId): ?array;
	public static function marketingEmailClone(string $emailId, string $newName): ?array;
	public static function marketingEmailSend(string $emailId, array $recipientIds): bool;
	public static function marketingEmailUpdate(string $emailId, array $data): bool;
	public static function marketingEmailStatistics(string $emailId): ?array;

	// Campaigns
	public static function campaignList(int $limit = 50, int $offset = 0): array;
	public static function campaignLoad(string $campaignId): ?array;

	// Files
	public static function fileUpload(string $filePath, string $fileName, array $options = []): array;
	public static function fileDelete(string $fileId): array;
	public static function fileLoad(string $fileId): array;
	public static function fileList(array $filters = []): array;
	public static function fileUpdate(string $fileId, array $updateData): array;
	public static function fileArchive(string $fileId): array;
	public static function fileReplace(string $fileId, string $newFilePath, string $newFileName = null): array;
	public static function fileGetSignedUrl(string $fileId): string;
	public static function fileSearch(string $query, array $options = []): array;

	public static function hubDbTableList(): array;

	// Hub DB
	public static function hubDbTableLoad(string $tableIdOrName): ?array;
	public static function hubDbTableCreate(array $tableData): ?array;
	public static function hubDbTableUpdate(string $tableIdOrName, array $tableData): bool;
	public static function hubDbTableDelete(string $tableIdOrName): bool;
	public static function hubDbTablePublish(string $tableIdOrName): bool;

	public static function hubDbTableColumns(string $tableIdOrName): array;
	public static function hubDbTableColumnCreate(string $tableIdOrName, array $columnData): ?array;
	public static function hubDbTableColumnUpdate(string $tableIdOrName, string $columnName, array $columnData): bool;
	public static function hubDbTableColumnDelete(string $tableIdOrName, string $columnName): bool;

	public static function hubDbTableRows(string $tableIdOrName, int $limit = 100, int $offset = 0): array;
	public static function hubDbTableRowLoad(string $tableIdOrName, string $rowId): ?array;
	public static function hubDbTableRowCreate(string $tableIdOrName, array $data): ?array;
	public static function hubDbTableRowUpdate(string $tableIdOrName, string $rowId, array $data): bool;
	public static function hubDbTableRowDelete(string $tableIdOrName, string $rowId): bool;

	public static function hubDbTableRowsDraft(string $tableIdOrName, int $limit = 100, int $offset = 0): array;
	public static function hubDbTableRowsPublished(string $tableIdOrName, int $limit = 100, int $offset = 0): array;

	// Tickets
	public static function ticketLoad(string $ticketId): ?array;
	public static function ticketSave(array $ticketData): ?array;
	public static function ticketDelete(string $ticketId): bool;
	public static function ticketSearch(array $filters = [], int $limit = 20, int $offset = 0): array;

	public static function propertyList(string $objectType): array;
	public static function propertyCreate(string $objectType, array $propertyData): ?array;
	public static function propertyUpdate(string $objectType, string $propertyName, array $propertyData): bool;
	public static function propertyDelete(string $objectType, string $propertyName): bool;

	// Subscription Management
	public static function webhookSubscriptionsList(string $appId): array;
	public static function webhookSubscriptionLoad(string $appId, string $subscriptionId): array;
	public static function webhookSubscriptionCreate(string $appId, array $subscriptionData): array;
	public static function webhookSubscriptionSave(string $appId, string $subscriptionId, array $updateData): array;
	public static function webhookSubscriptionDelete(string $appId, string $subscriptionId): array;

	// App-Level Settings (URL, Retry preferences, etc.)
	public static function webhookSettingsLoad(string $appId): array;
	public static function webhookSettingsUpdate(string $appId, array $settingsData): array;

	// Debugging & Logs
	public static function webhookRecentFailuresLoad(string $appId): array;
	public static function webhookEventResend(string $appId, string $eventId): array;
	public static function webhookEventStatusLoad(string $appId, string $eventId): array;

	// Workflows
	public static function workflowSearch(array $filters = []): array;
	public static function workflowLoad(string $workflowId): array;

	// Enroll someone into a Workflow
	public static function workflowEnrollContact(string $workflowId, string $email): array;

	// Analytics
	public static function analyticsTrafficSources(array $filters): array;
	public static function analyticsPageViews(array $filters): array;

}
