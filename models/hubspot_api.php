<?php

class hubspot_service {

	public static function getBaseUrl(): string;
	public static function getAccessToken(): string;

	public static function requestRaw(string $method, string $path, array $body = [], array $query = []): array;

	// Owner Information
	public static function ownerSearch(): array;
	public static function ownerLoad(string $id): ?array;

	// Contact Information
	public static function contactLoad(string $email): ?array;
	public static function contactSave(array $data): ?array;
	public static function contactDelete(string $email): bool;
	public static function contactLoad(string $id): ?array;
	public static function contactSearch(array $filters = [], int $limit = 20, int $offset = 0): array;

	// Company Information
	public static function companyLoad(string $domain): ?array;
	public static function companySave(array $data): ?array;
	public static function companyDelete(string $domain): bool;
	public static function companyLoad(string $id): ?array;
	public static function companySearch(array $filters = [], int $limit = 20, int $offset = 0): array;

	// Deal Information
	public static function dealLoad(string $dealName): ?array;
	public static function dealSave(array $data): ?array;
	public static function dealDelete(string $dealId): bool;
	public static function dealLoad(string $id): ?array;
	public static function dealSearch(array $filters = [], int $limit = 20, int $offset = 0): array;

	// Linking Companies, Contacts and Deals
	public static function associateContactWithCompany(string $contactId, string $companyId): bool;
	public static function associateContactWithDeal(string $contactId, string $dealId): bool;
	public static function associateDealWithCompany(string $dealId, string $companyId): bool;

	// Engagements
	public static function engagementCreate(array $data): ?array;
	public static function engagementDelete(string $engagementId): bool;
	public static function engagementSearch(array $filters = [], int $limit = 20, int $offset = 0): array;

	// Custom Objects
	public static function customObjectCreate(string $objectType, array $data): ?array;
	public static function customObjectLoad(string $objectType, string $id): ?array;
	public static function customObjectDelete(string $objectType, string $id): bool;

	// Products
	public static function productSearch(array $filters = [], int $limit = 20, int $offset = 0): array;
	public static function productCreate(array $productData): array;
	public static function productLoad(string $productId): array;
	public static function productSave(array $data): ?array;
	public static function productDelete(string $id): bool;

	// Product Properties (schema)
	public static function productPropertiesLoad(): array;
	public static function productPropertyCreate(array $propertyData): array;
	public static function productPropertySave(string $propertyName, array $propertyData): array;
	public static function productPropertyDelete(string $propertyName): array;

	// Product Folders
	public static function productFoldersLoad(): array;
	public static function productFolderCreate(array $folderData): array;
	public static function productFolderLoad(string $folderId): array;
	public static function productFolderSave(string $folderId, array $folderData): array;
	public static function productFolderDelete(string $folderId): array;

	// Form Definitions
	public static function formSearch(): array;
	public static function formSubmit(string $portalId, string $formGuid, array $fields, array $context = []): bool;
	public static function formLoad(string $formId): array;
	public static function formCreate(array $formData): array;
	public static function formSave(string $formId, array $formData): array;
	public static function formDelete(string $formId): array;
	public static function formSubmissionsLoad(string $formId, array $filters = []): array;

	// Form Performance
	public static function formPerformanceLoad(string $formId): array;

	// Form Fields (useful for form builder UIs)
	public static function formFieldsLoad(string $formId): array;
	
	public static function emailEvents(string $recipientEmail): array;

	// Files
	public static function fileUpload(string $filePath, string $fileName, array $options = []): array;
	public static function fileDelete(string $fileId): array;
	public static function fileLoad(string $fileId): array;
	public static function fileList(array $filters = []): array;
	public static function fileSave(string $fileId, array $updateData): array;
	public static function fileArchive(string $fileId): array;
	public static function fileReplace(string $fileId, string $newFilePath, string $newFileName = null): array;
	public static function fileGetSignedUrl(string $fileId): string;
	public static function fileSearch(string $query, array $options = []): array;

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
