<?php

class hubspot_service {

	public static function getBaseUrl(): string;
	public static function getAccessToken(): string;

	public static function requestRaw(string $method, string $path, array $body = [], array $query = []): array;

	// Owner Information
	public static function ownerSearch(): array;
	public static function ownerLoad(string $id): ?array;

	// Contact Information
	public static function contactLoadById(string $id): ?array;
	public static function contactLoadByEmail(string $email): ?array;
	public static function contactSearch(array $filters = [], int $limit = 20, int $offset = 0): array;
	public static function contactUpdate(array $data): ?array;
	public static function contactDelete(string $email): bool;

	// Company Information
	public static function companyLoadById(string $id): ?array;
	public static function companyLoadByName(string $domain): ?array;
	public static function companySearch(array $filters = [], int $limit = 20, int $offset = 0): array;
	public static function companyUpdate(array $data): ?array;
	public static function companyDelete(string $domain): bool;

	// Deal Information
	public static function dealLoadById(string $id): ?array;
	public static function dealLoadByName(string $dealName): ?array;
	public static function dealSearch(array $filters = [], int $limit = 20, int $offset = 0): array;
	public static function dealUpdate(array $data): ?array;
	public static function dealDelete(string $dealId): bool;

	// Deal Pipeline
	public static function dealPipelineSearch(): array;
	public static function dealPipelineLoad(string $pipelineId): array;
	public static function dealPipelineCreate(array $data): array;
	public static function dealPipelineUpdate(string $pipelineId, array $data): array;
	public static function dealPipelineDelete(string $pipelineId): array;

	// Deal Stage
	public static function dealPipelineStageSearch(string $pipelineId): array;
	public static function dealPipelineStageLoad(string $pipelineId, string $stageId): array;
	public static function dealPipelineStageCreate(string $pipelineId, array $data): array;
	public static function dealPipelineStageUpdate(string $pipelineId, string $stageId, array $data): array;
	public static function dealPipelineStageDelete(string $pipelineId, string $stageId): array;

	
	// Linking Companies, Contacts and Deals
	public static function associateContactWithCompany(string $contactId, string $companyId): bool;
	public static function associateContactWithDeal(string $contactId, string $dealId): bool;
	public static function associateDealWithCompany(string $dealId, string $companyId): bool;
	public static function associateDealWithContact(string $dealId, string $contactId): array;

	// Engagements
	public static function engagementCreate(array $data): ?array;
	public static function engagementDelete(string $engagementId): bool;
	public static function engagementSearch(array $filters = [], int $limit = 20, int $offset = 0): array;

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
}
