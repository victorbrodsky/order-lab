<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * appDevDebugProjectContainer.
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class appDevDebugProjectContainer extends Container
{
    private $parameters;
    private $targetDirs = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $dir = __DIR__;
        for ($i = 1; $i <= 5; ++$i) {
            $this->targetDirs[$i] = $dir = dirname($dir);
        }
        $this->parameters = $this->getDefaultParameters();

        $this->services = array();
        $this->methodMap = array(
            '1_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602' => 'get18fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602Service',
            '2_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602' => 'get28fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602Service',
            'annotation_reader' => 'getAnnotationReaderService',
            'annotations.reader' => 'getAnnotations_ReaderService',
            'argument_resolver.default' => 'getArgumentResolver_DefaultService',
            'argument_resolver.request' => 'getArgumentResolver_RequestService',
            'argument_resolver.request_attribute' => 'getArgumentResolver_RequestAttributeService',
            'argument_resolver.service' => 'getArgumentResolver_ServiceService',
            'argument_resolver.session' => 'getArgumentResolver_SessionService',
            'argument_resolver.variadic' => 'getArgumentResolver_VariadicService',
            'assetic.asset_factory' => 'getAssetic_AssetFactoryService',
            'assetic.asset_manager' => 'getAssetic_AssetManagerService',
            'assetic.controller' => 'getAssetic_ControllerService',
            'assetic.filter.cssrewrite' => 'getAssetic_Filter_CssrewriteService',
            'assetic.filter_manager' => 'getAssetic_FilterManagerService',
            'assetic.request_listener' => 'getAssetic_RequestListenerService',
            'assets.context' => 'getAssets_ContextService',
            'assets.packages' => 'getAssets_PackagesService',
            'authentication_handler' => 'getAuthenticationHandlerService',
            'cache.annotations' => 'getCache_AnnotationsService',
            'cache.annotations.recorder_inner' => 'getCache_Annotations_RecorderInnerService',
            'cache.app' => 'getCache_AppService',
            'cache.app.recorder_inner' => 'getCache_App_RecorderInnerService',
            'cache.default_clearer' => 'getCache_DefaultClearerService',
            'cache.global_clearer' => 'getCache_GlobalClearerService',
            'cache.serializer.recorder_inner' => 'getCache_Serializer_RecorderInnerService',
            'cache.system' => 'getCache_SystemService',
            'cache.system.recorder_inner' => 'getCache_System_RecorderInnerService',
            'cache.validator' => 'getCache_ValidatorService',
            'cache.validator.recorder_inner' => 'getCache_Validator_RecorderInnerService',
            'cache_clearer' => 'getCacheClearerService',
            'cache_warmer' => 'getCacheWarmerService',
            'calllog_authentication_handler' => 'getCalllogAuthenticationHandlerService',
            'calllog_permission_voter' => 'getCalllogPermissionVoterService',
            'calllog_role_voter' => 'getCalllogRoleVoterService',
            'calllog_util' => 'getCalllogUtilService',
            'calllog_util_form' => 'getCalllogUtilFormService',
            'config_cache_factory' => 'getConfigCacheFactoryService',
            'console.command.symfony_bundle_securitybundle_command_userpasswordencodercommand' => 'getConsole_Command_SymfonyBundleSecuritybundleCommandUserpasswordencodercommandService',
            'console.command.symfony_bundle_webserverbundle_command_serverruncommand' => 'getConsole_Command_SymfonyBundleWebserverbundleCommandServerruncommandService',
            'console.command.symfony_bundle_webserverbundle_command_serverstartcommand' => 'getConsole_Command_SymfonyBundleWebserverbundleCommandServerstartcommandService',
            'console.command.symfony_bundle_webserverbundle_command_serverstatuscommand' => 'getConsole_Command_SymfonyBundleWebserverbundleCommandServerstatuscommandService',
            'console.command.symfony_bundle_webserverbundle_command_serverstopcommand' => 'getConsole_Command_SymfonyBundleWebserverbundleCommandServerstopcommandService',
            'console.error_listener' => 'getConsole_ErrorListenerService',
            'controller_name_converter' => 'getControllerNameConverterService',
            'custom_authenticator' => 'getCustomAuthenticatorService',
            'data_collector.dump' => 'getDataCollector_DumpService',
            'data_collector.form' => 'getDataCollector_FormService',
            'data_collector.form.extractor' => 'getDataCollector_Form_ExtractorService',
            'data_collector.request' => 'getDataCollector_RequestService',
            'data_collector.router' => 'getDataCollector_RouterService',
            'debug.argument_resolver' => 'getDebug_ArgumentResolverService',
            'debug.controller_resolver' => 'getDebug_ControllerResolverService',
            'debug.debug_handlers_listener' => 'getDebug_DebugHandlersListenerService',
            'debug.dump_listener' => 'getDebug_DumpListenerService',
            'debug.event_dispatcher' => 'getDebug_EventDispatcherService',
            'debug.file_link_formatter' => 'getDebug_FileLinkFormatterService',
            'debug.log_processor' => 'getDebug_LogProcessorService',
            'debug.security.access.decision_manager' => 'getDebug_Security_Access_DecisionManagerService',
            'debug.stopwatch' => 'getDebug_StopwatchService',
            'deidentifier_authentication_handler' => 'getDeidentifierAuthenticationHandlerService',
            'deidentifier_permission_voter' => 'getDeidentifierPermissionVoterService',
            'deidentifier_role_voter' => 'getDeidentifierRoleVoterService',
            'deprecated.form.registry' => 'getDeprecated_Form_RegistryService',
            'deprecated.form.registry.csrf' => 'getDeprecated_Form_Registry_CsrfService',
            'doctrine' => 'getDoctrineService',
            'doctrine.dbal.aperio_connection' => 'getDoctrine_Dbal_AperioConnectionService',
            'doctrine.dbal.connection_factory' => 'getDoctrine_Dbal_ConnectionFactoryService',
            'doctrine.dbal.default_connection' => 'getDoctrine_Dbal_DefaultConnectionService',
            'doctrine.dbal.logger' => 'getDoctrine_Dbal_LoggerService',
            'doctrine.dbal.logger.profiling.aperio' => 'getDoctrine_Dbal_Logger_Profiling_AperioService',
            'doctrine.dbal.logger.profiling.default' => 'getDoctrine_Dbal_Logger_Profiling_DefaultService',
            'doctrine.listener' => 'getDoctrine_ListenerService',
            'doctrine.orm.aperio_entity_listener_resolver' => 'getDoctrine_Orm_AperioEntityListenerResolverService',
            'doctrine.orm.aperio_entity_manager' => 'getDoctrine_Orm_AperioEntityManagerService',
            'doctrine.orm.aperio_entity_manager.property_info_extractor' => 'getDoctrine_Orm_AperioEntityManager_PropertyInfoExtractorService',
            'doctrine.orm.aperio_listeners.attach_entity_listeners' => 'getDoctrine_Orm_AperioListeners_AttachEntityListenersService',
            'doctrine.orm.aperio_manager_configurator' => 'getDoctrine_Orm_AperioManagerConfiguratorService',
            'doctrine.orm.default_entity_listener_resolver' => 'getDoctrine_Orm_DefaultEntityListenerResolverService',
            'doctrine.orm.default_entity_manager' => 'getDoctrine_Orm_DefaultEntityManagerService',
            'doctrine.orm.default_entity_manager.property_info_extractor' => 'getDoctrine_Orm_DefaultEntityManager_PropertyInfoExtractorService',
            'doctrine.orm.default_listeners.attach_entity_listeners' => 'getDoctrine_Orm_DefaultListeners_AttachEntityListenersService',
            'doctrine.orm.default_manager_configurator' => 'getDoctrine_Orm_DefaultManagerConfiguratorService',
            'doctrine.orm.naming_strategy.default' => 'getDoctrine_Orm_NamingStrategy_DefaultService',
            'doctrine.orm.quote_strategy.default' => 'getDoctrine_Orm_QuoteStrategy_DefaultService',
            'doctrine.orm.validator.unique' => 'getDoctrine_Orm_Validator_UniqueService',
            'doctrine.orm.validator_initializer' => 'getDoctrine_Orm_ValidatorInitializerService',
            'doctrine_cache.providers.doctrine.orm.aperio_metadata_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_AperioMetadataCacheService',
            'doctrine_cache.providers.doctrine.orm.aperio_query_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_AperioQueryCacheService',
            'doctrine_cache.providers.doctrine.orm.aperio_result_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_AperioResultCacheService',
            'doctrine_cache.providers.doctrine.orm.default_metadata_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_DefaultMetadataCacheService',
            'doctrine_cache.providers.doctrine.orm.default_query_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_DefaultQueryCacheService',
            'doctrine_cache.providers.doctrine.orm.default_result_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_DefaultResultCacheService',
            'employees_authentication_handler' => 'getEmployeesAuthenticationHandlerService',
            'fellapp_authentication_handler' => 'getFellappAuthenticationHandlerService',
            'fellapp_googlesheetmanagement' => 'getFellappGooglesheetmanagementService',
            'fellapp_importpopulate_util' => 'getFellappImportpopulateUtilService',
            'fellapp_permission_voter' => 'getFellappPermissionVoterService',
            'fellapp_reportgenerator' => 'getFellappReportgeneratorService',
            'fellapp_role_voter' => 'getFellappRoleVoterService',
            'fellapp_util' => 'getFellappUtilService',
            'file_locator' => 'getFileLocatorService',
            'filesystem' => 'getFilesystemService',
            'form.factory' => 'getForm_FactoryService',
            'form.registry' => 'getForm_RegistryService',
            'form.resolved_type_factory' => 'getForm_ResolvedTypeFactoryService',
            'form.server_params' => 'getForm_ServerParamsService',
            'form.type.birthday' => 'getForm_Type_BirthdayService',
            'form.type.button' => 'getForm_Type_ButtonService',
            'form.type.checkbox' => 'getForm_Type_CheckboxService',
            'form.type.choice' => 'getForm_Type_ChoiceService',
            'form.type.collection' => 'getForm_Type_CollectionService',
            'form.type.country' => 'getForm_Type_CountryService',
            'form.type.currency' => 'getForm_Type_CurrencyService',
            'form.type.date' => 'getForm_Type_DateService',
            'form.type.datetime' => 'getForm_Type_DatetimeService',
            'form.type.email' => 'getForm_Type_EmailService',
            'form.type.entity' => 'getForm_Type_EntityService',
            'form.type.file' => 'getForm_Type_FileService',
            'form.type.form' => 'getForm_Type_FormService',
            'form.type.hidden' => 'getForm_Type_HiddenService',
            'form.type.integer' => 'getForm_Type_IntegerService',
            'form.type.language' => 'getForm_Type_LanguageService',
            'form.type.locale' => 'getForm_Type_LocaleService',
            'form.type.money' => 'getForm_Type_MoneyService',
            'form.type.number' => 'getForm_Type_NumberService',
            'form.type.password' => 'getForm_Type_PasswordService',
            'form.type.percent' => 'getForm_Type_PercentService',
            'form.type.radio' => 'getForm_Type_RadioService',
            'form.type.range' => 'getForm_Type_RangeService',
            'form.type.repeated' => 'getForm_Type_RepeatedService',
            'form.type.reset' => 'getForm_Type_ResetService',
            'form.type.search' => 'getForm_Type_SearchService',
            'form.type.submit' => 'getForm_Type_SubmitService',
            'form.type.text' => 'getForm_Type_TextService',
            'form.type.textarea' => 'getForm_Type_TextareaService',
            'form.type.time' => 'getForm_Type_TimeService',
            'form.type.timezone' => 'getForm_Type_TimezoneService',
            'form.type.url' => 'getForm_Type_UrlService',
            'form.type_extension.csrf' => 'getForm_TypeExtension_CsrfService',
            'form.type_extension.form.data_collector' => 'getForm_TypeExtension_Form_DataCollectorService',
            'form.type_extension.form.http_foundation' => 'getForm_TypeExtension_Form_HttpFoundationService',
            'form.type_extension.form.validator' => 'getForm_TypeExtension_Form_ValidatorService',
            'form.type_extension.repeated.validator' => 'getForm_TypeExtension_Repeated_ValidatorService',
            'form.type_extension.submit.validator' => 'getForm_TypeExtension_Submit_ValidatorService',
            'form.type_extension.upload.validator' => 'getForm_TypeExtension_Upload_ValidatorService',
            'form.type_guesser.doctrine' => 'getForm_TypeGuesser_DoctrineService',
            'form.type_guesser.validator' => 'getForm_TypeGuesser_ValidatorService',
            'fos_js_routing.controller' => 'getFosJsRouting_ControllerService',
            'fos_js_routing.extractor' => 'getFosJsRouting_ExtractorService',
            'fos_js_routing.serializer' => 'getFosJsRouting_SerializerService',
            'fos_user.change_password.form.factory' => 'getFosUser_ChangePassword_Form_FactoryService',
            'fos_user.change_password.form.type' => 'getFosUser_ChangePassword_Form_TypeService',
            'fos_user.listener.authentication' => 'getFosUser_Listener_AuthenticationService',
            'fos_user.listener.flash' => 'getFosUser_Listener_FlashService',
            'fos_user.listener.resetting' => 'getFosUser_Listener_ResettingService',
            'fos_user.mailer' => 'getFosUser_MailerService',
            'fos_user.profile.form.factory' => 'getFosUser_Profile_Form_FactoryService',
            'fos_user.profile.form.type' => 'getFosUser_Profile_Form_TypeService',
            'fos_user.registration.form.factory' => 'getFosUser_Registration_Form_FactoryService',
            'fos_user.registration.form.type' => 'getFosUser_Registration_Form_TypeService',
            'fos_user.resetting.form.factory' => 'getFosUser_Resetting_Form_FactoryService',
            'fos_user.resetting.form.type' => 'getFosUser_Resetting_Form_TypeService',
            'fos_user.security.interactive_login_listener' => 'getFosUser_Security_InteractiveLoginListenerService',
            'fos_user.security.login_manager' => 'getFosUser_Security_LoginManagerService',
            'fos_user.user_listener' => 'getFosUser_UserListenerService',
            'fos_user.user_manager' => 'getFosUser_UserManagerService',
            'fos_user.user_provider.username' => 'getFosUser_UserProvider_UsernameService',
            'fos_user.username_form_type' => 'getFosUser_UsernameFormTypeService',
            'fos_user.util.canonical_fields_updater' => 'getFosUser_Util_CanonicalFieldsUpdaterService',
            'fos_user.util.email_canonicalizer' => 'getFosUser_Util_EmailCanonicalizerService',
            'fos_user.util.password_updater' => 'getFosUser_Util_PasswordUpdaterService',
            'fos_user.util.token_generator' => 'getFosUser_Util_TokenGeneratorService',
            'fos_user.util.user_manipulator' => 'getFosUser_Util_UserManipulatorService',
            'fragment.handler' => 'getFragment_HandlerService',
            'fragment.listener' => 'getFragment_ListenerService',
            'fragment.renderer.esi' => 'getFragment_Renderer_EsiService',
            'fragment.renderer.hinclude' => 'getFragment_Renderer_HincludeService',
            'fragment.renderer.inline' => 'getFragment_Renderer_InlineService',
            'fragment.renderer.ssi' => 'getFragment_Renderer_SsiService',
            'html2pdf_factory' => 'getHtml2pdfFactoryService',
            'http_kernel' => 'getHttpKernelService',
            'kernel.class_cache.cache_warmer' => 'getKernel_ClassCache_CacheWarmerService',
            'knp_paginator' => 'getKnpPaginatorService',
            'knp_paginator.helper.processor' => 'getKnpPaginator_Helper_ProcessorService',
            'knp_paginator.subscriber.filtration' => 'getKnpPaginator_Subscriber_FiltrationService',
            'knp_paginator.subscriber.paginate' => 'getKnpPaginator_Subscriber_PaginateService',
            'knp_paginator.subscriber.sliding_pagination' => 'getKnpPaginator_Subscriber_SlidingPaginationService',
            'knp_paginator.subscriber.sortable' => 'getKnpPaginator_Subscriber_SortableService',
            'knp_paginator.twig.extension.pagination' => 'getKnpPaginator_Twig_Extension_PaginationService',
            'knp_snappy.image' => 'getKnpSnappy_ImageService',
            'knp_snappy.pdf' => 'getKnpSnappy_PdfService',
            'locale_listener' => 'getLocaleListenerService',
            'logger' => 'getLoggerService',
            'monolog.handler.console' => 'getMonolog_Handler_ConsoleService',
            'monolog.handler.file' => 'getMonolog_Handler_FileService',
            'monolog.handler.main' => 'getMonolog_Handler_MainService',
            'monolog.handler.null_internal' => 'getMonolog_Handler_NullInternalService',
            'monolog.handler.syslog' => 'getMonolog_Handler_SyslogService',
            'monolog.logger.assetic' => 'getMonolog_Logger_AsseticService',
            'monolog.logger.cache' => 'getMonolog_Logger_CacheService',
            'monolog.logger.console' => 'getMonolog_Logger_ConsoleService',
            'monolog.logger.doctrine' => 'getMonolog_Logger_DoctrineService',
            'monolog.logger.event' => 'getMonolog_Logger_EventService',
            'monolog.logger.php' => 'getMonolog_Logger_PhpService',
            'monolog.logger.profiler' => 'getMonolog_Logger_ProfilerService',
            'monolog.logger.request' => 'getMonolog_Logger_RequestService',
            'monolog.logger.router' => 'getMonolog_Logger_RouterService',
            'monolog.logger.security' => 'getMonolog_Logger_SecurityService',
            'monolog.logger.snappy' => 'getMonolog_Logger_SnappyService',
            'monolog.logger.templating' => 'getMonolog_Logger_TemplatingService',
            'monolog.processor.psr_log_message' => 'getMonolog_Processor_PsrLogMessageService',
            'oleg.handler.session_idle' => 'getOleg_Handler_SessionIdleService',
            'oleg.listener.maintenance' => 'getOleg_Listener_MaintenanceService',
            'oleg.twig.extension.date' => 'getOleg_Twig_Extension_DateService',
            'oleg.type.employees_custom_selector' => 'getOleg_Type_EmployeesCustomSelectorService',
            'oleg.upload_listener' => 'getOleg_UploadListenerService',
            'oneup_uploader.chunk_manager' => 'getOneupUploader_ChunkManagerService',
            'oneup_uploader.chunks_storage' => 'getOneupUploader_ChunksStorageService',
            'oneup_uploader.controller.employees_gallery' => 'getOneupUploader_Controller_EmployeesGalleryService',
            'oneup_uploader.controller.fellapp_gallery' => 'getOneupUploader_Controller_FellappGalleryService',
            'oneup_uploader.controller.scan_gallery' => 'getOneupUploader_Controller_ScanGalleryService',
            'oneup_uploader.controller.vacreq_gallery' => 'getOneupUploader_Controller_VacreqGalleryService',
            'oneup_uploader.error_handler.dropzone' => 'getOneupUploader_ErrorHandler_DropzoneService',
            'oneup_uploader.namer.uniqid' => 'getOneupUploader_Namer_UniqidService',
            'oneup_uploader.orphanage_manager' => 'getOneupUploader_OrphanageManagerService',
            'oneup_uploader.routing.loader' => 'getOneupUploader_Routing_LoaderService',
            'oneup_uploader.storage.employees_gallery' => 'getOneupUploader_Storage_EmployeesGalleryService',
            'oneup_uploader.storage.fellapp_gallery' => 'getOneupUploader_Storage_FellappGalleryService',
            'oneup_uploader.storage.scan_gallery' => 'getOneupUploader_Storage_ScanGalleryService',
            'oneup_uploader.storage.vacreq_gallery' => 'getOneupUploader_Storage_VacreqGalleryService',
            'oneup_uploader.templating.uploader_helper' => 'getOneupUploader_Templating_UploaderHelperService',
            'oneup_uploader.twig.extension.uploader' => 'getOneupUploader_Twig_Extension_UploaderService',
            'oneup_uploader.validation_listener.allowed_mimetype' => 'getOneupUploader_ValidationListener_AllowedMimetypeService',
            'oneup_uploader.validation_listener.disallowed_mimetype' => 'getOneupUploader_ValidationListener_DisallowedMimetypeService',
            'oneup_uploader.validation_listener.max_size' => 'getOneupUploader_ValidationListener_MaxSizeService',
            'order_form.type.scan_custom_selector' => 'getOrderForm_Type_ScanCustomSelectorService',
            'order_security_utility' => 'getOrderSecurityUtilityService',
            'profiler' => 'getProfilerService',
            'profiler_listener' => 'getProfilerListenerService',
            'property_accessor' => 'getPropertyAccessorService',
            'request_stack' => 'getRequestStackService',
            'resolve_controller_name_subscriber' => 'getResolveControllerNameSubscriberService',
            'response_listener' => 'getResponseListenerService',
            'router' => 'getRouterService',
            'router.request_context' => 'getRouter_RequestContextService',
            'router_listener' => 'getRouterListenerService',
            'routing.loader' => 'getRouting_LoaderService',
            'scan_permission_voter' => 'getScanPermissionVoterService',
            'scan_role_voter' => 'getScanRoleVoterService',
            'scanorder_utility' => 'getScanorderUtilityService',
            'search_utility' => 'getSearchUtilityService',
            'security.access.authenticated_voter' => 'getSecurity_Access_AuthenticatedVoterService',
            'security.access.expression_voter' => 'getSecurity_Access_ExpressionVoterService',
            'security.access.role_hierarchy_voter' => 'getSecurity_Access_RoleHierarchyVoterService',
            'security.access_listener' => 'getSecurity_AccessListenerService',
            'security.access_map' => 'getSecurity_AccessMapService',
            'security.authentication.guard_handler' => 'getSecurity_Authentication_GuardHandlerService',
            'security.authentication.manager' => 'getSecurity_Authentication_ManagerService',
            'security.authentication.provider.anonymous.aperio_ldap_firewall' => 'getSecurity_Authentication_Provider_Anonymous_AperioLdapFirewallService',
            'security.authentication.provider.anonymous.ldap_calllog_firewall' => 'getSecurity_Authentication_Provider_Anonymous_LdapCalllogFirewallService',
            'security.authentication.provider.anonymous.ldap_deidentifier_firewall' => 'getSecurity_Authentication_Provider_Anonymous_LdapDeidentifierFirewallService',
            'security.authentication.provider.anonymous.ldap_employees_firewall' => 'getSecurity_Authentication_Provider_Anonymous_LdapEmployeesFirewallService',
            'security.authentication.provider.anonymous.ldap_fellapp_firewall' => 'getSecurity_Authentication_Provider_Anonymous_LdapFellappFirewallService',
            'security.authentication.provider.anonymous.ldap_translationalresearch_firewall' => 'getSecurity_Authentication_Provider_Anonymous_LdapTranslationalresearchFirewallService',
            'security.authentication.provider.anonymous.ldap_vacreq_firewall' => 'getSecurity_Authentication_Provider_Anonymous_LdapVacreqFirewallService',
            'security.authentication.provider.rememberme.aperio_ldap_firewall' => 'getSecurity_Authentication_Provider_Rememberme_AperioLdapFirewallService',
            'security.authentication.provider.rememberme.ldap_calllog_firewall' => 'getSecurity_Authentication_Provider_Rememberme_LdapCalllogFirewallService',
            'security.authentication.provider.rememberme.ldap_deidentifier_firewall' => 'getSecurity_Authentication_Provider_Rememberme_LdapDeidentifierFirewallService',
            'security.authentication.provider.rememberme.ldap_employees_firewall' => 'getSecurity_Authentication_Provider_Rememberme_LdapEmployeesFirewallService',
            'security.authentication.provider.rememberme.ldap_fellapp_firewall' => 'getSecurity_Authentication_Provider_Rememberme_LdapFellappFirewallService',
            'security.authentication.provider.rememberme.ldap_translationalresearch_firewall' => 'getSecurity_Authentication_Provider_Rememberme_LdapTranslationalresearchFirewallService',
            'security.authentication.provider.rememberme.ldap_vacreq_firewall' => 'getSecurity_Authentication_Provider_Rememberme_LdapVacreqFirewallService',
            'security.authentication.provider.simple_form.aperio_ldap_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_AperioLdapFirewallService',
            'security.authentication.provider.simple_form.ldap_calllog_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_LdapCalllogFirewallService',
            'security.authentication.provider.simple_form.ldap_deidentifier_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_LdapDeidentifierFirewallService',
            'security.authentication.provider.simple_form.ldap_employees_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_LdapEmployeesFirewallService',
            'security.authentication.provider.simple_form.ldap_fellapp_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_LdapFellappFirewallService',
            'security.authentication.provider.simple_form.ldap_translationalresearch_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_LdapTranslationalresearchFirewallService',
            'security.authentication.provider.simple_form.ldap_vacreq_firewall' => 'getSecurity_Authentication_Provider_SimpleForm_LdapVacreqFirewallService',
            'security.authentication.session_strategy' => 'getSecurity_Authentication_SessionStrategyService',
            'security.authentication.trust_resolver' => 'getSecurity_Authentication_TrustResolverService',
            'security.authentication_utils' => 'getSecurity_AuthenticationUtilsService',
            'security.authorization_checker' => 'getSecurity_AuthorizationCheckerService',
            'security.channel_listener' => 'getSecurity_ChannelListenerService',
            'security.context_listener.0' => 'getSecurity_ContextListener_0Service',
            'security.csrf.token_manager' => 'getSecurity_Csrf_TokenManagerService',
            'security.encoder_factory' => 'getSecurity_EncoderFactoryService',
            'security.firewall' => 'getSecurity_FirewallService',
            'security.firewall.map' => 'getSecurity_Firewall_MapService',
            'security.firewall.map.context.aperio_ldap_firewall' => 'getSecurity_Firewall_Map_Context_AperioLdapFirewallService',
            'security.firewall.map.context.ldap_calllog_firewall' => 'getSecurity_Firewall_Map_Context_LdapCalllogFirewallService',
            'security.firewall.map.context.ldap_deidentifier_firewall' => 'getSecurity_Firewall_Map_Context_LdapDeidentifierFirewallService',
            'security.firewall.map.context.ldap_employees_firewall' => 'getSecurity_Firewall_Map_Context_LdapEmployeesFirewallService',
            'security.firewall.map.context.ldap_fellapp_firewall' => 'getSecurity_Firewall_Map_Context_LdapFellappFirewallService',
            'security.firewall.map.context.ldap_translationalresearch_firewall' => 'getSecurity_Firewall_Map_Context_LdapTranslationalresearchFirewallService',
            'security.firewall.map.context.ldap_vacreq_firewall' => 'getSecurity_Firewall_Map_Context_LdapVacreqFirewallService',
            'security.http_utils' => 'getSecurity_HttpUtilsService',
            'security.logout.handler.session' => 'getSecurity_Logout_Handler_SessionService',
            'security.logout_url_generator' => 'getSecurity_LogoutUrlGeneratorService',
            'security.password_encoder' => 'getSecurity_PasswordEncoderService',
            'security.rememberme.response_listener' => 'getSecurity_Rememberme_ResponseListenerService',
            'security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e' => 'getSecurity_RequestMatcher_2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7eService',
            'security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee' => 'getSecurity_RequestMatcher_96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daeeService',
            'security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1' => 'getSecurity_RequestMatcher_Ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1Service',
            'security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe' => 'getSecurity_RequestMatcher_Af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffeService',
            'security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5' => 'getSecurity_RequestMatcher_D9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5Service',
            'security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776' => 'getSecurity_RequestMatcher_Eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776Service',
            'security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f' => 'getSecurity_RequestMatcher_Fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1fService',
            'security.role_hierarchy' => 'getSecurity_RoleHierarchyService',
            'security.token_storage' => 'getSecurity_TokenStorageService',
            'security.user_checker' => 'getSecurity_UserCheckerService',
            'security.user_value_resolver' => 'getSecurity_UserValueResolverService',
            'security.validator.user_password' => 'getSecurity_Validator_UserPasswordService',
            'sensio_distribution.security_checker' => 'getSensioDistribution_SecurityCheckerService',
            'sensio_distribution.security_checker.command' => 'getSensioDistribution_SecurityChecker_CommandService',
            'sensio_framework_extra.cache.listener' => 'getSensioFrameworkExtra_Cache_ListenerService',
            'sensio_framework_extra.controller.listener' => 'getSensioFrameworkExtra_Controller_ListenerService',
            'sensio_framework_extra.converter.datetime' => 'getSensioFrameworkExtra_Converter_DatetimeService',
            'sensio_framework_extra.converter.doctrine.orm' => 'getSensioFrameworkExtra_Converter_Doctrine_OrmService',
            'sensio_framework_extra.converter.listener' => 'getSensioFrameworkExtra_Converter_ListenerService',
            'sensio_framework_extra.converter.manager' => 'getSensioFrameworkExtra_Converter_ManagerService',
            'sensio_framework_extra.security.listener' => 'getSensioFrameworkExtra_Security_ListenerService',
            'sensio_framework_extra.view.guesser' => 'getSensioFrameworkExtra_View_GuesserService',
            'sensio_framework_extra.view.listener' => 'getSensioFrameworkExtra_View_ListenerService',
            'service_locator.e64d23c3bf770e2cf44b71643280668d' => 'getServiceLocator_E64d23c3bf770e2cf44b71643280668dService',
            'session' => 'getSessionService',
            'session.handler' => 'getSession_HandlerService',
            'session.save_listener' => 'getSession_SaveListenerService',
            'session.storage.filesystem' => 'getSession_Storage_FilesystemService',
            'session.storage.metadata_bag' => 'getSession_Storage_MetadataBagService',
            'session.storage.native' => 'getSession_Storage_NativeService',
            'session.storage.php_bridge' => 'getSession_Storage_PhpBridgeService',
            'session_listener' => 'getSessionListenerService',
            'spraed.pdf.generator' => 'getSpraed_Pdf_GeneratorService',
            'stof_doctrine_extensions.uploadable.manager' => 'getStofDoctrineExtensions_Uploadable_ManagerService',
            'streamed_response_listener' => 'getStreamedResponseListenerService',
            'swiftmailer.email_sender.listener' => 'getSwiftmailer_EmailSender_ListenerService',
            'swiftmailer.mailer.default' => 'getSwiftmailer_Mailer_DefaultService',
            'swiftmailer.mailer.default.plugin.messagelogger' => 'getSwiftmailer_Mailer_Default_Plugin_MessageloggerService',
            'swiftmailer.mailer.default.spool' => 'getSwiftmailer_Mailer_Default_SpoolService',
            'swiftmailer.mailer.default.transport' => 'getSwiftmailer_Mailer_Default_TransportService',
            'swiftmailer.mailer.default.transport.eventdispatcher' => 'getSwiftmailer_Mailer_Default_Transport_EventdispatcherService',
            'swiftmailer.mailer.default.transport.real' => 'getSwiftmailer_Mailer_Default_Transport_RealService',
            'swiftmailer.plugin.redirecting' => 'getSwiftmailer_Plugin_RedirectingService',
            'templating' => 'getTemplatingService',
            'templating.filename_parser' => 'getTemplating_FilenameParserService',
            'templating.helper.logout_url' => 'getTemplating_Helper_LogoutUrlService',
            'templating.helper.security' => 'getTemplating_Helper_SecurityService',
            'templating.loader' => 'getTemplating_LoaderService',
            'templating.locator' => 'getTemplating_LocatorService',
            'templating.name_parser' => 'getTemplating_NameParserService',
            'translationalresearch_authentication_handler' => 'getTranslationalresearchAuthenticationHandlerService',
            'translationalresearch_permission_voter' => 'getTranslationalresearchPermissionVoterService',
            'translationalresearch_role_voter' => 'getTranslationalresearchRoleVoterService',
            'translator' => 'getTranslatorService',
            'twig' => 'getTwigService',
            'twig.controller.exception' => 'getTwig_Controller_ExceptionService',
            'twig.controller.preview_error' => 'getTwig_Controller_PreviewErrorService',
            'twig.exception_listener' => 'getTwig_ExceptionListenerService',
            'twig.form.renderer' => 'getTwig_Form_RendererService',
            'twig.loader' => 'getTwig_LoaderService',
            'twig.profile' => 'getTwig_ProfileService',
            'twig.runtime.httpkernel' => 'getTwig_Runtime_HttpkernelService',
            'twig.translation.extractor' => 'getTwig_Translation_ExtractorService',
            'twigdate.listener.request' => 'getTwigdate_Listener_RequestService',
            'uri_signer' => 'getUriSignerService',
            'user_download_utility' => 'getUserDownloadUtilityService',
            'user_formnode_utility' => 'getUserFormnodeUtilityService',
            'user_generator' => 'getUserGeneratorService',
            'user_mailer_utility' => 'getUserMailerUtilityService',
            'user_permission_voter' => 'getUserPermissionVoterService',
            'user_role_voter' => 'getUserRoleVoterService',
            'user_security_utility' => 'getUserSecurityUtilityService',
            'user_service_utility' => 'getUserServiceUtilityService',
            'vacreq_authentication_handler' => 'getVacreqAuthenticationHandlerService',
            'vacreq_awaycalendar_listener' => 'getVacreqAwaycalendarListenerService',
            'vacreq_import_data' => 'getVacreqImportDataService',
            'vacreq_permission_voter' => 'getVacreqPermissionVoterService',
            'vacreq_role_voter' => 'getVacreqRoleVoterService',
            'vacreq_util' => 'getVacreqUtilService',
            'validate_request_listener' => 'getValidateRequestListenerService',
            'validator' => 'getValidatorService',
            'validator.builder' => 'getValidator_BuilderService',
            'validator.email' => 'getValidator_EmailService',
            'validator.expression' => 'getValidator_ExpressionService',
            'var_dumper.cli_dumper' => 'getVarDumper_CliDumperService',
            'var_dumper.cloner' => 'getVarDumper_ClonerService',
            'web_profiler.controller.exception' => 'getWebProfiler_Controller_ExceptionService',
            'web_profiler.controller.profiler' => 'getWebProfiler_Controller_ProfilerService',
            'web_profiler.controller.router' => 'getWebProfiler_Controller_RouterService',
            'web_profiler.csp.handler' => 'getWebProfiler_Csp_HandlerService',
            'web_profiler.debug_toolbar' => 'getWebProfiler_DebugToolbarService',
        );
        $this->privates = array(
            '1_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602' => true,
            '2_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602' => true,
            'annotations.reader' => true,
            'argument_resolver.default' => true,
            'argument_resolver.request' => true,
            'argument_resolver.request_attribute' => true,
            'argument_resolver.service' => true,
            'argument_resolver.session' => true,
            'argument_resolver.variadic' => true,
            'assetic.asset_factory' => true,
            'cache.annotations' => true,
            'cache.annotations.recorder_inner' => true,
            'cache.app.recorder_inner' => true,
            'cache.serializer.recorder_inner' => true,
            'cache.system.recorder_inner' => true,
            'cache.validator' => true,
            'cache.validator.recorder_inner' => true,
            'calllog_permission_voter' => true,
            'calllog_role_voter' => true,
            'console.error_listener' => true,
            'controller_name_converter' => true,
            'debug.file_link_formatter' => true,
            'debug.log_processor' => true,
            'debug.security.access.decision_manager' => true,
            'deidentifier_permission_voter' => true,
            'deidentifier_role_voter' => true,
            'doctrine.dbal.logger' => true,
            'doctrine.dbal.logger.profiling.aperio' => true,
            'doctrine.dbal.logger.profiling.default' => true,
            'doctrine.orm.naming_strategy.default' => true,
            'doctrine.orm.quote_strategy.default' => true,
            'fellapp_permission_voter' => true,
            'fellapp_role_voter' => true,
            'form.server_params' => true,
            'form.type.choice' => true,
            'form.type.form' => true,
            'form.type_extension.csrf' => true,
            'form.type_extension.form.data_collector' => true,
            'form.type_extension.form.http_foundation' => true,
            'form.type_extension.form.validator' => true,
            'form.type_extension.repeated.validator' => true,
            'form.type_extension.submit.validator' => true,
            'form.type_extension.upload.validator' => true,
            'form.type_guesser.validator' => true,
            'fos_user.user_listener' => true,
            'fos_user.user_provider.username' => true,
            'fos_user.util.canonical_fields_updater' => true,
            'fos_user.util.password_updater' => true,
            'monolog.processor.psr_log_message' => true,
            'oneup_uploader.error_handler.dropzone' => true,
            'resolve_controller_name_subscriber' => true,
            'router.request_context' => true,
            'scan_permission_voter' => true,
            'scan_role_voter' => true,
            'security.access.authenticated_voter' => true,
            'security.access.expression_voter' => true,
            'security.access.role_hierarchy_voter' => true,
            'security.access_listener' => true,
            'security.access_map' => true,
            'security.authentication.manager' => true,
            'security.authentication.provider.anonymous.aperio_ldap_firewall' => true,
            'security.authentication.provider.anonymous.ldap_calllog_firewall' => true,
            'security.authentication.provider.anonymous.ldap_deidentifier_firewall' => true,
            'security.authentication.provider.anonymous.ldap_employees_firewall' => true,
            'security.authentication.provider.anonymous.ldap_fellapp_firewall' => true,
            'security.authentication.provider.anonymous.ldap_translationalresearch_firewall' => true,
            'security.authentication.provider.anonymous.ldap_vacreq_firewall' => true,
            'security.authentication.provider.rememberme.aperio_ldap_firewall' => true,
            'security.authentication.provider.rememberme.ldap_calllog_firewall' => true,
            'security.authentication.provider.rememberme.ldap_deidentifier_firewall' => true,
            'security.authentication.provider.rememberme.ldap_employees_firewall' => true,
            'security.authentication.provider.rememberme.ldap_fellapp_firewall' => true,
            'security.authentication.provider.rememberme.ldap_translationalresearch_firewall' => true,
            'security.authentication.provider.rememberme.ldap_vacreq_firewall' => true,
            'security.authentication.provider.simple_form.aperio_ldap_firewall' => true,
            'security.authentication.provider.simple_form.ldap_calllog_firewall' => true,
            'security.authentication.provider.simple_form.ldap_deidentifier_firewall' => true,
            'security.authentication.provider.simple_form.ldap_employees_firewall' => true,
            'security.authentication.provider.simple_form.ldap_fellapp_firewall' => true,
            'security.authentication.provider.simple_form.ldap_translationalresearch_firewall' => true,
            'security.authentication.provider.simple_form.ldap_vacreq_firewall' => true,
            'security.authentication.session_strategy' => true,
            'security.authentication.trust_resolver' => true,
            'security.channel_listener' => true,
            'security.context_listener.0' => true,
            'security.firewall.map' => true,
            'security.http_utils' => true,
            'security.logout.handler.session' => true,
            'security.logout_url_generator' => true,
            'security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e' => true,
            'security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee' => true,
            'security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1' => true,
            'security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe' => true,
            'security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5' => true,
            'security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776' => true,
            'security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f' => true,
            'security.role_hierarchy' => true,
            'security.user_checker' => true,
            'security.user_value_resolver' => true,
            'service_locator.e64d23c3bf770e2cf44b71643280668d' => true,
            'session.storage.metadata_bag' => true,
            'swiftmailer.mailer.default.transport.eventdispatcher' => true,
            'templating.locator' => true,
            'translationalresearch_permission_voter' => true,
            'translationalresearch_role_voter' => true,
            'user_permission_voter' => true,
            'user_role_voter' => true,
            'vacreq_permission_voter' => true,
            'vacreq_role_voter' => true,
            'web_profiler.csp.handler' => true,
        );
        $this->aliases = array(
            'cache.app_clearer' => 'cache.default_clearer',
            'database_connection' => 'doctrine.dbal.default_connection',
            'doctrine.orm.aperio_metadata_cache' => 'doctrine_cache.providers.doctrine.orm.aperio_metadata_cache',
            'doctrine.orm.aperio_query_cache' => 'doctrine_cache.providers.doctrine.orm.aperio_query_cache',
            'doctrine.orm.aperio_result_cache' => 'doctrine_cache.providers.doctrine.orm.aperio_result_cache',
            'doctrine.orm.default_metadata_cache' => 'doctrine_cache.providers.doctrine.orm.default_metadata_cache',
            'doctrine.orm.default_query_cache' => 'doctrine_cache.providers.doctrine.orm.default_query_cache',
            'doctrine.orm.default_result_cache' => 'doctrine_cache.providers.doctrine.orm.default_result_cache',
            'doctrine.orm.entity_manager' => 'doctrine.orm.default_entity_manager',
            'event_dispatcher' => 'debug.event_dispatcher',
            'fos_user.util.username_canonicalizer' => 'fos_user.util.email_canonicalizer',
            'mailer' => 'swiftmailer.mailer.default',
            'session.storage' => 'session.storage.native',
            'swiftmailer.mailer' => 'swiftmailer.mailer.default',
            'swiftmailer.plugin.messagelogger' => 'swiftmailer.mailer.default.plugin.messagelogger',
            'swiftmailer.spool' => 'swiftmailer.mailer.default.spool',
            'swiftmailer.transport' => 'swiftmailer.mailer.default.transport',
            'swiftmailer.transport.real' => 'swiftmailer.mailer.default.transport.real',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    /**
     * {@inheritdoc}
     */
    public function isCompiled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since version 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }

    /**
     * Gets the public 'annotation_reader' shared service.
     *
     * @return \Doctrine\Common\Annotations\CachedReader
     */
    protected function getAnnotationReaderService()
    {
        return $this->services['annotation_reader'] = new \Doctrine\Common\Annotations\CachedReader(${($_ = isset($this->services['annotations.reader']) ? $this->services['annotations.reader'] : $this->getAnnotations_ReaderService()) && false ?: '_'}, new \Symfony\Component\Cache\DoctrineProvider(\Symfony\Component\Cache\Adapter\PhpArrayAdapter::create((__DIR__.'/annotations.php'), ${($_ = isset($this->services['cache.annotations']) ? $this->services['cache.annotations'] : $this->getCache_AnnotationsService()) && false ?: '_'})), true);
    }

    /**
     * Gets the public 'assetic.asset_manager' shared service.
     *
     * @return \Assetic\Factory\LazyAssetManager
     */
    protected function getAssetic_AssetManagerService()
    {
        $a = ${($_ = isset($this->services['templating.loader']) ? $this->services['templating.loader'] : $this->get('templating.loader')) && false ?: '_'};

        $this->services['assetic.asset_manager'] = $instance = new \Assetic\Factory\LazyAssetManager(${($_ = isset($this->services['assetic.asset_factory']) ? $this->services['assetic.asset_factory'] : $this->getAssetic_AssetFactoryService()) && false ?: '_'}, array('twig' => new \Assetic\Factory\Loader\CachedFormulaLoader(new \Assetic\Extension\Twig\TwigFormulaLoader(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.assetic']) ? $this->services['monolog.logger.assetic'] : $this->get('monolog.logger.assetic', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}), new \Assetic\Cache\ConfigCache((__DIR__.'/assetic/config')), true)));

        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegUserdirectoryBundle', ($this->targetDirs[3].'\\app/Resources/OlegUserdirectoryBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegUserdirectoryBundle', ($this->targetDirs[3].'\\src\\Oleg\\UserdirectoryBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegOrderformBundle', ($this->targetDirs[3].'\\app/Resources/OlegOrderformBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegOrderformBundle', ($this->targetDirs[3].'\\src\\Oleg\\OrderformBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegFellAppBundle', ($this->targetDirs[3].'\\app/Resources/OlegFellAppBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegFellAppBundle', ($this->targetDirs[3].'\\src\\Oleg\\FellAppBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegDeidentifierBundle', ($this->targetDirs[3].'\\app/Resources/OlegDeidentifierBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegDeidentifierBundle', ($this->targetDirs[3].'\\src\\Oleg\\DeidentifierBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegVacReqBundle', ($this->targetDirs[3].'\\app/Resources/OlegVacReqBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegVacReqBundle', ($this->targetDirs[3].'\\src\\Oleg\\VacReqBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegCallLogBundle', ($this->targetDirs[3].'\\app/Resources/OlegCallLogBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegCallLogBundle', ($this->targetDirs[3].'\\src\\Oleg\\CallLogBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegTranslationalResearchBundle', ($this->targetDirs[3].'\\app/Resources/OlegTranslationalResearchBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OlegTranslationalResearchBundle', ($this->targetDirs[3].'\\src\\Oleg\\TranslationalResearchBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, '', ($this->targetDirs[3].'\\app/Resources/views'), '/\\.[^.]+\\.twig$/'), 'twig');

        return $instance;
    }

    /**
     * Gets the public 'assetic.controller' shared service.
     *
     * @return \Symfony\Bundle\AsseticBundle\Controller\AsseticController
     */
    protected function getAssetic_ControllerService()
    {
        return $this->services['assetic.controller'] = new \Symfony\Bundle\AsseticBundle\Controller\AsseticController(${($_ = isset($this->services['assetic.asset_manager']) ? $this->services['assetic.asset_manager'] : $this->get('assetic.asset_manager')) && false ?: '_'}, new \Assetic\Cache\FilesystemCache((__DIR__.'/assetic/assets')), false, ${($_ = isset($this->services['profiler']) ? $this->services['profiler'] : $this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'assetic.filter.cssrewrite' shared service.
     *
     * @return \Assetic\Filter\CssRewriteFilter
     */
    protected function getAssetic_Filter_CssrewriteService()
    {
        return $this->services['assetic.filter.cssrewrite'] = new \Assetic\Filter\CssRewriteFilter();
    }

    /**
     * Gets the public 'assetic.filter_manager' shared service.
     *
     * @return \Symfony\Bundle\AsseticBundle\FilterManager
     */
    protected function getAssetic_FilterManagerService()
    {
        return $this->services['assetic.filter_manager'] = new \Symfony\Bundle\AsseticBundle\FilterManager($this, array('cssrewrite' => 'assetic.filter.cssrewrite'));
    }

    /**
     * Gets the public 'assetic.request_listener' shared service.
     *
     * @return \Symfony\Bundle\AsseticBundle\EventListener\RequestListener
     */
    protected function getAssetic_RequestListenerService()
    {
        return $this->services['assetic.request_listener'] = new \Symfony\Bundle\AsseticBundle\EventListener\RequestListener();
    }

    /**
     * Gets the public 'assets.context' shared service.
     *
     * @return \Symfony\Component\Asset\Context\RequestStackContext
     */
    protected function getAssets_ContextService()
    {
        return $this->services['assets.context'] = new \Symfony\Component\Asset\Context\RequestStackContext(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'});
    }

    /**
     * Gets the public 'assets.packages' shared service.
     *
     * @return \Symfony\Component\Asset\Packages
     */
    protected function getAssets_PackagesService()
    {
        return $this->services['assets.packages'] = new \Symfony\Component\Asset\Packages(new \Symfony\Component\Asset\PathPackage('', new \Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy(), ${($_ = isset($this->services['assets.context']) ? $this->services['assets.context'] : $this->get('assets.context')) && false ?: '_'}), array());
    }

    /**
     * Gets the public 'authentication_handler' shared service.
     *
     * @return \Oleg\OrderformBundle\Security\Authentication\ScanLoginSuccessHandler
     */
    protected function getAuthenticationHandlerService()
    {
        return $this->services['authentication_handler'] = new \Oleg\OrderformBundle\Security\Authentication\ScanLoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'cache.app' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCache_AppService()
    {
        return $this->services['cache.app'] = new \Symfony\Component\Cache\Adapter\TraceableAdapter(${($_ = isset($this->services['cache.app.recorder_inner']) ? $this->services['cache.app.recorder_inner'] : $this->getCache_App_RecorderInnerService()) && false ?: '_'});
    }

    /**
     * Gets the public 'cache.default_clearer' shared service.
     *
     * @return \Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer
     */
    protected function getCache_DefaultClearerService()
    {
        return $this->services['cache.default_clearer'] = new \Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer(array('cache.app' => ${($_ = isset($this->services['cache.app']) ? $this->services['cache.app'] : $this->get('cache.app')) && false ?: '_'}, 'cache.system' => ${($_ = isset($this->services['cache.system']) ? $this->services['cache.system'] : $this->get('cache.system')) && false ?: '_'}, 'cache.validator' => ${($_ = isset($this->services['cache.validator']) ? $this->services['cache.validator'] : $this->getCache_ValidatorService()) && false ?: '_'}, 'cache.annotations' => ${($_ = isset($this->services['cache.annotations']) ? $this->services['cache.annotations'] : $this->getCache_AnnotationsService()) && false ?: '_'}));
    }

    /**
     * Gets the public 'cache.global_clearer' shared service.
     *
     * @return \Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer
     */
    protected function getCache_GlobalClearerService()
    {
        return $this->services['cache.global_clearer'] = new \Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer(array('cache.app' => ${($_ = isset($this->services['cache.app']) ? $this->services['cache.app'] : $this->get('cache.app')) && false ?: '_'}, 'cache.system' => ${($_ = isset($this->services['cache.system']) ? $this->services['cache.system'] : $this->get('cache.system')) && false ?: '_'}, 'cache.validator' => ${($_ = isset($this->services['cache.validator']) ? $this->services['cache.validator'] : $this->getCache_ValidatorService()) && false ?: '_'}, 'cache.annotations' => ${($_ = isset($this->services['cache.annotations']) ? $this->services['cache.annotations'] : $this->getCache_AnnotationsService()) && false ?: '_'}));
    }

    /**
     * Gets the public 'cache.system' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCache_SystemService()
    {
        return $this->services['cache.system'] = new \Symfony\Component\Cache\Adapter\TraceableAdapter(${($_ = isset($this->services['cache.system.recorder_inner']) ? $this->services['cache.system.recorder_inner'] : $this->getCache_System_RecorderInnerService()) && false ?: '_'});
    }

    /**
     * Gets the public 'cache_clearer' shared service.
     *
     * @return \Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer
     */
    protected function getCacheClearerService()
    {
        return $this->services['cache_clearer'] = new \Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer(array(0 => ${($_ = isset($this->services['cache.default_clearer']) ? $this->services['cache.default_clearer'] : $this->get('cache.default_clearer')) && false ?: '_'}));
    }

    /**
     * Gets the public 'cache_warmer' shared service.
     *
     * @return \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate
     */
    protected function getCacheWarmerService()
    {
        $a = ${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'};
        $b = ${($_ = isset($this->services['templating.filename_parser']) ? $this->services['templating.filename_parser'] : $this->get('templating.filename_parser')) && false ?: '_'};

        $c = new \Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinder($a, $b, ($this->targetDirs[3].'\\app/Resources'));

        return $this->services['cache_warmer'] = new \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate(array(0 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplatePathsCacheWarmer($c, ${($_ = isset($this->services['templating.locator']) ? $this->services['templating.locator'] : $this->getTemplating_LocatorService()) && false ?: '_'}), 1 => new \Symfony\Bundle\AsseticBundle\CacheWarmer\AssetManagerCacheWarmer($this), 2 => ${($_ = isset($this->services['kernel.class_cache.cache_warmer']) ? $this->services['kernel.class_cache.cache_warmer'] : $this->get('kernel.class_cache.cache_warmer')) && false ?: '_'}, 3 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\ValidatorCacheWarmer(${($_ = isset($this->services['validator.builder']) ? $this->services['validator.builder'] : $this->get('validator.builder')) && false ?: '_'}, (__DIR__.'/validation.php'), ${($_ = isset($this->services['cache.validator']) ? $this->services['cache.validator'] : $this->getCache_ValidatorService()) && false ?: '_'}), 4 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\RouterCacheWarmer(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}), 5 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\AnnotationsCacheWarmer(${($_ = isset($this->services['annotations.reader']) ? $this->services['annotations.reader'] : $this->getAnnotations_ReaderService()) && false ?: '_'}, (__DIR__.'/annotations.php'), ${($_ = isset($this->services['cache.annotations']) ? $this->services['cache.annotations'] : $this->getCache_AnnotationsService()) && false ?: '_'}), 6 => new \Symfony\Bundle\TwigBundle\CacheWarmer\TemplateCacheCacheWarmer(new \Symfony\Component\DependencyInjection\ServiceLocator(array('twig' => function () {
            $f = function (\Twig\Environment $v) { return $v; }; return $f(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'});
        })), $c, array()), 7 => new \Symfony\Bundle\TwigBundle\CacheWarmer\TemplateCacheWarmer($this, new \Symfony\Bundle\TwigBundle\TemplateIterator($a, ($this->targetDirs[3].'\\app'), array())), 8 => new \Symfony\Bridge\Doctrine\CacheWarmer\ProxyCacheWarmer(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'})));
    }

    /**
     * Gets the public 'calllog_authentication_handler' shared service.
     *
     * @return \Oleg\CallLogBundle\Security\Authentication\CallLogLoginSuccessHandler
     */
    protected function getCalllogAuthenticationHandlerService()
    {
        return $this->services['calllog_authentication_handler'] = new \Oleg\CallLogBundle\Security\Authentication\CallLogLoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'calllog_util' shared service.
     *
     * @return \Oleg\CallLogBundle\Util\CallLogUtil
     */
    protected function getCalllogUtilService()
    {
        return $this->services['calllog_util'] = new \Oleg\CallLogBundle\Util\CallLogUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'calllog_util_form' shared service.
     *
     * @return \Oleg\CallLogBundle\Util\CallLogUtilForm
     */
    protected function getCalllogUtilFormService()
    {
        return $this->services['calllog_util_form'] = new \Oleg\CallLogBundle\Util\CallLogUtilForm(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'config_cache_factory' shared service.
     *
     * @return \Symfony\Component\Config\ResourceCheckerConfigCacheFactory
     */
    protected function getConfigCacheFactoryService()
    {
        return $this->services['config_cache_factory'] = new \Symfony\Component\Config\ResourceCheckerConfigCacheFactory(new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['1_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602']) ? $this->services['1_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602'] : $this->get18fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602Service()) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['2_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602']) ? $this->services['2_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602'] : $this->get28fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602Service()) && false ?: '_'};
        }, 2));
    }

    /**
     * Gets the public 'console.command.symfony_bundle_securitybundle_command_userpasswordencodercommand' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Command\UserPasswordEncoderCommand
     */
    protected function getConsole_Command_SymfonyBundleSecuritybundleCommandUserpasswordencodercommandService()
    {
        return $this->services['console.command.symfony_bundle_securitybundle_command_userpasswordencodercommand'] = new \Symfony\Bundle\SecurityBundle\Command\UserPasswordEncoderCommand(${($_ = isset($this->services['security.encoder_factory']) ? $this->services['security.encoder_factory'] : $this->get('security.encoder_factory')) && false ?: '_'}, array(0 => 'FOS\\UserBundle\\Model\\UserInterface'));
    }

    /**
     * Gets the public 'console.command.symfony_bundle_webserverbundle_command_serverruncommand' shared service.
     *
     * @return \Symfony\Bundle\WebServerBundle\Command\ServerRunCommand
     */
    protected function getConsole_Command_SymfonyBundleWebserverbundleCommandServerruncommandService()
    {
        return $this->services['console.command.symfony_bundle_webserverbundle_command_serverruncommand'] = new \Symfony\Bundle\WebServerBundle\Command\ServerRunCommand(($this->targetDirs[3].'/public'), 'dev');
    }

    /**
     * Gets the public 'console.command.symfony_bundle_webserverbundle_command_serverstartcommand' shared service.
     *
     * @return \Symfony\Bundle\WebServerBundle\Command\ServerStartCommand
     */
    protected function getConsole_Command_SymfonyBundleWebserverbundleCommandServerstartcommandService()
    {
        return $this->services['console.command.symfony_bundle_webserverbundle_command_serverstartcommand'] = new \Symfony\Bundle\WebServerBundle\Command\ServerStartCommand(($this->targetDirs[3].'/public'), 'dev');
    }

    /**
     * Gets the public 'console.command.symfony_bundle_webserverbundle_command_serverstatuscommand' shared service.
     *
     * @return \Symfony\Bundle\WebServerBundle\Command\ServerStatusCommand
     */
    protected function getConsole_Command_SymfonyBundleWebserverbundleCommandServerstatuscommandService()
    {
        return $this->services['console.command.symfony_bundle_webserverbundle_command_serverstatuscommand'] = new \Symfony\Bundle\WebServerBundle\Command\ServerStatusCommand();
    }

    /**
     * Gets the public 'console.command.symfony_bundle_webserverbundle_command_serverstopcommand' shared service.
     *
     * @return \Symfony\Bundle\WebServerBundle\Command\ServerStopCommand
     */
    protected function getConsole_Command_SymfonyBundleWebserverbundleCommandServerstopcommandService()
    {
        return $this->services['console.command.symfony_bundle_webserverbundle_command_serverstopcommand'] = new \Symfony\Bundle\WebServerBundle\Command\ServerStopCommand();
    }

    /**
     * Gets the public 'custom_authenticator' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Security\Authentication\CustomAuthenticator
     */
    protected function getCustomAuthenticatorService()
    {
        return $this->services['custom_authenticator'] = new \Oleg\UserdirectoryBundle\Security\Authentication\CustomAuthenticator(${($_ = isset($this->services['security.password_encoder']) ? $this->services['security.password_encoder'] : $this->get('security.password_encoder')) && false ?: '_'}, $this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'data_collector.dump' shared service.
     *
     * @return \Symfony\Component\HttpKernel\DataCollector\DumpDataCollector
     */
    protected function getDataCollector_DumpService()
    {
        return $this->services['data_collector.dump'] = new \Symfony\Component\HttpKernel\DataCollector\DumpDataCollector(${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['debug.file_link_formatter']) ? $this->services['debug.file_link_formatter'] : $this->getDebug_FileLinkFormatterService()) && false ?: '_'}, 'UTF-8', ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, NULL);
    }

    /**
     * Gets the public 'data_collector.form' shared service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\FormDataCollector
     */
    protected function getDataCollector_FormService()
    {
        return $this->services['data_collector.form'] = new \Symfony\Component\Form\Extension\DataCollector\FormDataCollector(${($_ = isset($this->services['data_collector.form.extractor']) ? $this->services['data_collector.form.extractor'] : $this->get('data_collector.form.extractor')) && false ?: '_'});
    }

    /**
     * Gets the public 'data_collector.form.extractor' shared service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\FormDataExtractor
     */
    protected function getDataCollector_Form_ExtractorService()
    {
        return $this->services['data_collector.form.extractor'] = new \Symfony\Component\Form\Extension\DataCollector\FormDataExtractor();
    }

    /**
     * Gets the public 'data_collector.request' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector
     */
    protected function getDataCollector_RequestService()
    {
        return $this->services['data_collector.request'] = new \Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector();
    }

    /**
     * Gets the public 'data_collector.router' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\DataCollector\RouterDataCollector
     */
    protected function getDataCollector_RouterService()
    {
        return $this->services['data_collector.router'] = new \Symfony\Bundle\FrameworkBundle\DataCollector\RouterDataCollector();
    }

    /**
     * Gets the public 'debug.argument_resolver' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\TraceableArgumentResolver
     */
    protected function getDebug_ArgumentResolverService()
    {
        return $this->services['debug.argument_resolver'] = new \Symfony\Component\HttpKernel\Controller\TraceableArgumentResolver(new \Symfony\Component\HttpKernel\Controller\ArgumentResolver(new \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory(), new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['argument_resolver.request_attribute']) ? $this->services['argument_resolver.request_attribute'] : $this->getArgumentResolver_RequestAttributeService()) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['argument_resolver.request']) ? $this->services['argument_resolver.request'] : $this->getArgumentResolver_RequestService()) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['argument_resolver.session']) ? $this->services['argument_resolver.session'] : $this->getArgumentResolver_SessionService()) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['security.user_value_resolver']) ? $this->services['security.user_value_resolver'] : $this->getSecurity_UserValueResolverService()) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['argument_resolver.service']) ? $this->services['argument_resolver.service'] : $this->getArgumentResolver_ServiceService()) && false ?: '_'};
            yield 5 => ${($_ = isset($this->services['argument_resolver.default']) ? $this->services['argument_resolver.default'] : $this->getArgumentResolver_DefaultService()) && false ?: '_'};
            yield 6 => ${($_ = isset($this->services['argument_resolver.variadic']) ? $this->services['argument_resolver.variadic'] : $this->getArgumentResolver_VariadicService()) && false ?: '_'};
        }, 7)), ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch')) && false ?: '_'});
    }

    /**
     * Gets the public 'debug.controller_resolver' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver
     */
    protected function getDebug_ControllerResolverService()
    {
        return $this->services['debug.controller_resolver'] = new \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver(new \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver($this, ${($_ = isset($this->services['controller_name_converter']) ? $this->services['controller_name_converter'] : $this->getControllerNameConverterService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.request']) ? $this->services['monolog.logger.request'] : $this->get('monolog.logger.request', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}), ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch')) && false ?: '_'}, ${($_ = isset($this->services['debug.argument_resolver']) ? $this->services['debug.argument_resolver'] : $this->get('debug.argument_resolver')) && false ?: '_'});
    }

    /**
     * Gets the public 'debug.debug_handlers_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener
     */
    protected function getDebug_DebugHandlersListenerService()
    {
        return $this->services['debug.debug_handlers_listener'] = new \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener(NULL, ${($_ = isset($this->services['monolog.logger.php']) ? $this->services['monolog.logger.php'] : $this->get('monolog.logger.php', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, -1, -1, true, ${($_ = isset($this->services['debug.file_link_formatter']) ? $this->services['debug.file_link_formatter'] : $this->getDebug_FileLinkFormatterService()) && false ?: '_'}, true);
    }

    /**
     * Gets the public 'debug.dump_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\DumpListener
     */
    protected function getDebug_DumpListenerService()
    {
        return $this->services['debug.dump_listener'] = new \Symfony\Component\HttpKernel\EventListener\DumpListener(${($_ = isset($this->services['var_dumper.cloner']) ? $this->services['var_dumper.cloner'] : $this->get('var_dumper.cloner')) && false ?: '_'}, ${($_ = isset($this->services['var_dumper.cli_dumper']) ? $this->services['var_dumper.cli_dumper'] : $this->get('var_dumper.cli_dumper')) && false ?: '_'});
    }

    /**
     * Gets the public 'debug.event_dispatcher' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected function getDebug_EventDispatcherService()
    {
        $this->services['debug.event_dispatcher'] = $instance = new \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher(new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($this), ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch')) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.event']) ? $this->services['monolog.logger.event'] : $this->get('monolog.logger.event', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});

        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['oleg.listener.maintenance']) ? $this->services['oleg.listener.maintenance'] : $this->get('oleg.listener.maintenance')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['oleg.handler.session_idle']) ? $this->services['oleg.handler.session_idle'] : $this->get('oleg.handler.session_idle')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['twigdate.listener.request']) ? $this->services['twigdate.listener.request'] : $this->get('twigdate.listener.request')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 0);
        $instance->addListener('oneup_uploader.post_persist', array(0 => function () {
            return ${($_ = isset($this->services['oleg.upload_listener']) ? $this->services['oleg.upload_listener'] : $this->get('oleg.upload_listener')) && false ?: '_'};
        }, 1 => 'onUpload'), 0);
        $instance->addListener('calendar.load_events', array(0 => function () {
            return ${($_ = isset($this->services['vacreq_awaycalendar_listener']) ? $this->services['vacreq_awaycalendar_listener'] : $this->get('vacreq_awaycalendar_listener')) && false ?: '_'};
        }, 1 => 'loadEvents'), 0);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['data_collector.router']) ? $this->services['data_collector.router'] : $this->get('data_collector.router')) && false ?: '_'};
        }, 1 => 'onKernelController'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['assetic.request_listener']) ? $this->services['assetic.request_listener'] : $this->get('assetic.request_listener')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['knp_paginator.subscriber.sliding_pagination']) ? $this->services['knp_paginator.subscriber.sliding_pagination'] : $this->get('knp_paginator.subscriber.sliding_pagination')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 0);
        $instance->addListener('oneup_uploader.validation', array(0 => function () {
            return ${($_ = isset($this->services['oneup_uploader.validation_listener.max_size']) ? $this->services['oneup_uploader.validation_listener.max_size'] : $this->get('oneup_uploader.validation_listener.max_size')) && false ?: '_'};
        }, 1 => 'onValidate'), 0);
        $instance->addListener('oneup_uploader.validation', array(0 => function () {
            return ${($_ = isset($this->services['oneup_uploader.validation_listener.allowed_mimetype']) ? $this->services['oneup_uploader.validation_listener.allowed_mimetype'] : $this->get('oneup_uploader.validation_listener.allowed_mimetype')) && false ?: '_'};
        }, 1 => 'onValidate'), 0);
        $instance->addListener('oneup_uploader.validation', array(0 => function () {
            return ${($_ = isset($this->services['oneup_uploader.validation_listener.disallowed_mimetype']) ? $this->services['oneup_uploader.validation_listener.disallowed_mimetype'] : $this->get('oneup_uploader.validation_listener.disallowed_mimetype')) && false ?: '_'};
        }, 1 => 'onValidate'), 0);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['response_listener']) ? $this->services['response_listener'] : $this->get('response_listener')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), 0);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['streamed_response_listener']) ? $this->services['streamed_response_listener'] : $this->get('streamed_response_listener')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), -1024);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['locale_listener']) ? $this->services['locale_listener'] : $this->get('locale_listener')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 16);
        $instance->addListener('kernel.finish_request', array(0 => function () {
            return ${($_ = isset($this->services['locale_listener']) ? $this->services['locale_listener'] : $this->get('locale_listener')) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['validate_request_listener']) ? $this->services['validate_request_listener'] : $this->get('validate_request_listener')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 256);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['resolve_controller_name_subscriber']) ? $this->services['resolve_controller_name_subscriber'] : $this->getResolveControllerNameSubscriberService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 24);
        $instance->addListener('console.error', array(0 => function () {
            return ${($_ = isset($this->services['console.error_listener']) ? $this->services['console.error_listener'] : $this->getConsole_ErrorListenerService()) && false ?: '_'};
        }, 1 => 'onConsoleError'), -128);
        $instance->addListener('console.terminate', array(0 => function () {
            return ${($_ = isset($this->services['console.error_listener']) ? $this->services['console.error_listener'] : $this->getConsole_ErrorListenerService()) && false ?: '_'};
        }, 1 => 'onConsoleTerminate'), -128);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['session_listener']) ? $this->services['session_listener'] : $this->get('session_listener')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 128);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['session.save_listener']) ? $this->services['session.save_listener'] : $this->get('session.save_listener')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), -1000);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['fragment.listener']) ? $this->services['fragment.listener'] : $this->get('fragment.listener')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 48);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['profiler_listener']) ? $this->services['profiler_listener'] : $this->get('profiler_listener')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), -100);
        $instance->addListener('kernel.exception', array(0 => function () {
            return ${($_ = isset($this->services['profiler_listener']) ? $this->services['profiler_listener'] : $this->get('profiler_listener')) && false ?: '_'};
        }, 1 => 'onKernelException'), 0);
        $instance->addListener('kernel.terminate', array(0 => function () {
            return ${($_ = isset($this->services['profiler_listener']) ? $this->services['profiler_listener'] : $this->get('profiler_listener')) && false ?: '_'};
        }, 1 => 'onKernelTerminate'), -1024);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['data_collector.request']) ? $this->services['data_collector.request'] : $this->get('data_collector.request')) && false ?: '_'};
        }, 1 => 'onKernelController'), 0);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['data_collector.request']) ? $this->services['data_collector.request'] : $this->get('data_collector.request')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['debug.debug_handlers_listener']) ? $this->services['debug.debug_handlers_listener'] : $this->get('debug.debug_handlers_listener')) && false ?: '_'};
        }, 1 => 'configure'), 2048);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['router_listener']) ? $this->services['router_listener'] : $this->get('router_listener')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 32);
        $instance->addListener('kernel.finish_request', array(0 => function () {
            return ${($_ = isset($this->services['router_listener']) ? $this->services['router_listener'] : $this->get('router_listener')) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'), 0);
        $instance->addListener('kernel.request', array(0 => function () {
            return ${($_ = isset($this->services['security.firewall']) ? $this->services['security.firewall'] : $this->get('security.firewall')) && false ?: '_'};
        }, 1 => 'onKernelRequest'), 8);
        $instance->addListener('kernel.finish_request', array(0 => function () {
            return ${($_ = isset($this->services['security.firewall']) ? $this->services['security.firewall'] : $this->get('security.firewall')) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'), 0);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['security.rememberme.response_listener']) ? $this->services['security.rememberme.response_listener'] : $this->get('security.rememberme.response_listener')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), 0);
        $instance->addListener('kernel.exception', array(0 => function () {
            return ${($_ = isset($this->services['twig.exception_listener']) ? $this->services['twig.exception_listener'] : $this->get('twig.exception_listener')) && false ?: '_'};
        }, 1 => 'onKernelException'), -128);
        $instance->addListener('console.command', array(0 => function () {
            return ${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'};
        }, 1 => 'onCommand'), 255);
        $instance->addListener('console.terminate', array(0 => function () {
            return ${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'};
        }, 1 => 'onTerminate'), -255);
        $instance->addListener('kernel.exception', array(0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->get('swiftmailer.email_sender.listener')) && false ?: '_'};
        }, 1 => 'onException'), 0);
        $instance->addListener('kernel.terminate', array(0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->get('swiftmailer.email_sender.listener')) && false ?: '_'};
        }, 1 => 'onTerminate'), 0);
        $instance->addListener('console.error', array(0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->get('swiftmailer.email_sender.listener')) && false ?: '_'};
        }, 1 => 'onException'), 0);
        $instance->addListener('console.terminate', array(0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->get('swiftmailer.email_sender.listener')) && false ?: '_'};
        }, 1 => 'onTerminate'), 0);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.controller.listener']) ? $this->services['sensio_framework_extra.controller.listener'] : $this->get('sensio_framework_extra.controller.listener')) && false ?: '_'};
        }, 1 => 'onKernelController'), 0);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.converter.listener']) ? $this->services['sensio_framework_extra.converter.listener'] : $this->get('sensio_framework_extra.converter.listener')) && false ?: '_'};
        }, 1 => 'onKernelController'), 0);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.view.listener']) ? $this->services['sensio_framework_extra.view.listener'] : $this->get('sensio_framework_extra.view.listener')) && false ?: '_'};
        }, 1 => 'onKernelController'), -128);
        $instance->addListener('kernel.view', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.view.listener']) ? $this->services['sensio_framework_extra.view.listener'] : $this->get('sensio_framework_extra.view.listener')) && false ?: '_'};
        }, 1 => 'onKernelView'), 0);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.cache.listener']) ? $this->services['sensio_framework_extra.cache.listener'] : $this->get('sensio_framework_extra.cache.listener')) && false ?: '_'};
        }, 1 => 'onKernelController'), 0);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.cache.listener']) ? $this->services['sensio_framework_extra.cache.listener'] : $this->get('sensio_framework_extra.cache.listener')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), 0);
        $instance->addListener('kernel.controller', array(0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.security.listener']) ? $this->services['sensio_framework_extra.security.listener'] : $this->get('sensio_framework_extra.security.listener')) && false ?: '_'};
        }, 1 => 'onKernelController'), 0);
        $instance->addListener('fos_user.security.implicit_login', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.security.interactive_login_listener']) ? $this->services['fos_user.security.interactive_login_listener'] : $this->get('fos_user.security.interactive_login_listener')) && false ?: '_'};
        }, 1 => 'onImplicitLogin'), 0);
        $instance->addListener('security.interactive_login', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.security.interactive_login_listener']) ? $this->services['fos_user.security.interactive_login_listener'] : $this->get('fos_user.security.interactive_login_listener')) && false ?: '_'};
        }, 1 => 'onSecurityInteractiveLogin'), 0);
        $instance->addListener('fos_user.registration.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.authentication']) ? $this->services['fos_user.listener.authentication'] : $this->get('fos_user.listener.authentication')) && false ?: '_'};
        }, 1 => 'authenticate'), 0);
        $instance->addListener('fos_user.registration.confirmed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.authentication']) ? $this->services['fos_user.listener.authentication'] : $this->get('fos_user.listener.authentication')) && false ?: '_'};
        }, 1 => 'authenticate'), 0);
        $instance->addListener('fos_user.resetting.reset.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.authentication']) ? $this->services['fos_user.listener.authentication'] : $this->get('fos_user.listener.authentication')) && false ?: '_'};
        }, 1 => 'authenticate'), 0);
        $instance->addListener('fos_user.change_password.edit.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.group.create.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.group.delete.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.group.edit.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.profile.edit.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.registration.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.resetting.reset.completed', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.flash']) ? $this->services['fos_user.listener.flash'] : $this->get('fos_user.listener.flash')) && false ?: '_'};
        }, 1 => 'addSuccessFlash'), 0);
        $instance->addListener('fos_user.resetting.reset.initialize', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.resetting']) ? $this->services['fos_user.listener.resetting'] : $this->get('fos_user.listener.resetting')) && false ?: '_'};
        }, 1 => 'onResettingResetInitialize'), 0);
        $instance->addListener('fos_user.resetting.reset.success', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.resetting']) ? $this->services['fos_user.listener.resetting'] : $this->get('fos_user.listener.resetting')) && false ?: '_'};
        }, 1 => 'onResettingResetSuccess'), 0);
        $instance->addListener('fos_user.resetting.reset.request', array(0 => function () {
            return ${($_ = isset($this->services['fos_user.listener.resetting']) ? $this->services['fos_user.listener.resetting'] : $this->get('fos_user.listener.resetting')) && false ?: '_'};
        }, 1 => 'onResettingResetRequest'), 0);
        $instance->addListener('console.command', array(0 => function () {
            return ${($_ = isset($this->services['debug.dump_listener']) ? $this->services['debug.dump_listener'] : $this->get('debug.dump_listener')) && false ?: '_'};
        }, 1 => 'configure'), 1024);
        $instance->addListener('kernel.response', array(0 => function () {
            return ${($_ = isset($this->services['web_profiler.debug_toolbar']) ? $this->services['web_profiler.debug_toolbar'] : $this->get('web_profiler.debug_toolbar')) && false ?: '_'};
        }, 1 => 'onKernelResponse'), -128);
        $instance->addListener('knp_pager.before', array(0 => function () {
            return ${($_ = isset($this->services['knp_paginator.subscriber.paginate']) ? $this->services['knp_paginator.subscriber.paginate'] : $this->get('knp_paginator.subscriber.paginate')) && false ?: '_'};
        }, 1 => 'before'), 0);
        $instance->addListener('knp_pager.pagination', array(0 => function () {
            return ${($_ = isset($this->services['knp_paginator.subscriber.paginate']) ? $this->services['knp_paginator.subscriber.paginate'] : $this->get('knp_paginator.subscriber.paginate')) && false ?: '_'};
        }, 1 => 'pagination'), 0);
        $instance->addListener('knp_pager.before', array(0 => function () {
            return ${($_ = isset($this->services['knp_paginator.subscriber.sortable']) ? $this->services['knp_paginator.subscriber.sortable'] : $this->get('knp_paginator.subscriber.sortable')) && false ?: '_'};
        }, 1 => 'before'), 1);
        $instance->addListener('knp_pager.before', array(0 => function () {
            return ${($_ = isset($this->services['knp_paginator.subscriber.filtration']) ? $this->services['knp_paginator.subscriber.filtration'] : $this->get('knp_paginator.subscriber.filtration')) && false ?: '_'};
        }, 1 => 'before'), 1);
        $instance->addListener('knp_pager.pagination', array(0 => function () {
            return ${($_ = isset($this->services['knp_paginator.subscriber.sliding_pagination']) ? $this->services['knp_paginator.subscriber.sliding_pagination'] : $this->get('knp_paginator.subscriber.sliding_pagination')) && false ?: '_'};
        }, 1 => 'pagination'), 1);

        return $instance;
    }

    /**
     * Gets the public 'debug.stopwatch' shared service.
     *
     * @return \Symfony\Component\Stopwatch\Stopwatch
     */
    protected function getDebug_StopwatchService()
    {
        return $this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch();
    }

    /**
     * Gets the public 'deidentifier_authentication_handler' shared service.
     *
     * @return \Oleg\DeidentifierBundle\Security\Authentication\DeidentifierLoginSuccessHandler
     */
    protected function getDeidentifierAuthenticationHandlerService()
    {
        return $this->services['deidentifier_authentication_handler'] = new \Oleg\DeidentifierBundle\Security\Authentication\DeidentifierLoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'deprecated.form.registry' shared service.
     *
     * @return \stdClass
     *
     * @deprecated The service "deprecated.form.registry" is internal and deprecated since Symfony 3.3 and will be removed in Symfony 4.0
     */
    protected function getDeprecated_Form_RegistryService()
    {
        @trigger_error('The service "deprecated.form.registry" is internal and deprecated since Symfony 3.3 and will be removed in Symfony 4.0', E_USER_DEPRECATED);

        $this->services['deprecated.form.registry'] = $instance = new \stdClass();

        $instance->registry = array(0 => ${($_ = isset($this->services['form.type_guesser.validator']) ? $this->services['form.type_guesser.validator'] : $this->getForm_TypeGuesser_ValidatorService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['form.type.choice']) ? $this->services['form.type.choice'] : $this->getForm_Type_ChoiceService()) && false ?: '_'}, 2 => ${($_ = isset($this->services['form.type.form']) ? $this->services['form.type.form'] : $this->getForm_Type_FormService()) && false ?: '_'}, 3 => ${($_ = isset($this->services['form.type_extension.form.http_foundation']) ? $this->services['form.type_extension.form.http_foundation'] : $this->getForm_TypeExtension_Form_HttpFoundationService()) && false ?: '_'}, 4 => ${($_ = isset($this->services['form.type_extension.form.validator']) ? $this->services['form.type_extension.form.validator'] : $this->getForm_TypeExtension_Form_ValidatorService()) && false ?: '_'}, 5 => ${($_ = isset($this->services['form.type_extension.repeated.validator']) ? $this->services['form.type_extension.repeated.validator'] : $this->getForm_TypeExtension_Repeated_ValidatorService()) && false ?: '_'}, 6 => ${($_ = isset($this->services['form.type_extension.submit.validator']) ? $this->services['form.type_extension.submit.validator'] : $this->getForm_TypeExtension_Submit_ValidatorService()) && false ?: '_'}, 7 => ${($_ = isset($this->services['form.type_extension.upload.validator']) ? $this->services['form.type_extension.upload.validator'] : $this->getForm_TypeExtension_Upload_ValidatorService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'deprecated.form.registry.csrf' shared service.
     *
     * @return \stdClass
     *
     * @deprecated The service "deprecated.form.registry.csrf" is internal and deprecated since Symfony 3.3 and will be removed in Symfony 4.0
     */
    protected function getDeprecated_Form_Registry_CsrfService()
    {
        @trigger_error('The service "deprecated.form.registry.csrf" is internal and deprecated since Symfony 3.3 and will be removed in Symfony 4.0', E_USER_DEPRECATED);

        $this->services['deprecated.form.registry.csrf'] = $instance = new \stdClass();

        $instance->registry = array(0 => ${($_ = isset($this->services['form.type_extension.csrf']) ? $this->services['form.type_extension.csrf'] : $this->getForm_TypeExtension_CsrfService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'doctrine' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrineService()
    {
        return $this->services['doctrine'] = new \Doctrine\Bundle\DoctrineBundle\Registry($this, array('default' => 'doctrine.dbal.default_connection', 'aperio' => 'doctrine.dbal.aperio_connection'), array('default' => 'doctrine.orm.default_entity_manager', 'aperio' => 'doctrine.orm.aperio_entity_manager'), 'default', 'default');
    }

    /**
     * Gets the public 'doctrine.dbal.aperio_connection' shared service.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDoctrine_Dbal_AperioConnectionService()
    {
        $a = new \Doctrine\DBAL\Logging\LoggerChain();
        $a->addLogger(${($_ = isset($this->services['doctrine.dbal.logger']) ? $this->services['doctrine.dbal.logger'] : $this->getDoctrine_Dbal_LoggerService()) && false ?: '_'});
        $a->addLogger(${($_ = isset($this->services['doctrine.dbal.logger.profiling.aperio']) ? $this->services['doctrine.dbal.logger.profiling.aperio'] : $this->getDoctrine_Dbal_Logger_Profiling_AperioService()) && false ?: '_'});

        $b = new \Doctrine\DBAL\Configuration();
        $b->setSQLLogger($a);

        $c = new \Symfony\Bridge\Doctrine\ContainerAwareEventManager($this);
        $c->addEventSubscriber(${($_ = isset($this->services['fos_user.user_listener']) ? $this->services['fos_user.user_listener'] : $this->getFosUser_UserListenerService()) && false ?: '_'});
        $c->addEventListener(array(0 => 'loadClassMetadata'), ${($_ = isset($this->services['doctrine.orm.aperio_listeners.attach_entity_listeners']) ? $this->services['doctrine.orm.aperio_listeners.attach_entity_listeners'] : $this->get('doctrine.orm.aperio_listeners.attach_entity_listeners')) && false ?: '_'});

        return $this->services['doctrine.dbal.aperio_connection'] = ${($_ = isset($this->services['doctrine.dbal.connection_factory']) ? $this->services['doctrine.dbal.connection_factory'] : $this->get('doctrine.dbal.connection_factory')) && false ?: '_'}->createConnection(array('driver' => 'pdo_sqlsrv', 'host' => 'c.med.cornell.edu', 'port' => NULL, 'dbname' => 'Aperio', 'user' => 'symfony2_aperio', 'password' => 'Symfony!2', 'charset' => 'UTF8', 'driverOptions' => array(), 'defaultTableOptions' => array()), $b, $c, array());
    }

    /**
     * Gets the public 'doctrine.dbal.connection_factory' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\ConnectionFactory
     */
    protected function getDoctrine_Dbal_ConnectionFactoryService()
    {
        return $this->services['doctrine.dbal.connection_factory'] = new \Doctrine\Bundle\DoctrineBundle\ConnectionFactory(array());
    }

    /**
     * Gets the public 'doctrine.dbal.default_connection' shared service.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDoctrine_Dbal_DefaultConnectionService()
    {
        $a = new \Doctrine\DBAL\Logging\LoggerChain();
        $a->addLogger(${($_ = isset($this->services['doctrine.dbal.logger']) ? $this->services['doctrine.dbal.logger'] : $this->getDoctrine_Dbal_LoggerService()) && false ?: '_'});
        $a->addLogger(${($_ = isset($this->services['doctrine.dbal.logger.profiling.default']) ? $this->services['doctrine.dbal.logger.profiling.default'] : $this->getDoctrine_Dbal_Logger_Profiling_DefaultService()) && false ?: '_'});

        $b = new \Doctrine\DBAL\Configuration();
        $b->setSQLLogger($a);

        $c = new \Gedmo\Tree\TreeListener();
        $c->setAnnotationReader(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->get('annotation_reader')) && false ?: '_'});

        $d = new \Symfony\Bridge\Doctrine\ContainerAwareEventManager($this);
        $d->addEventSubscriber(${($_ = isset($this->services['fos_user.user_listener']) ? $this->services['fos_user.user_listener'] : $this->getFosUser_UserListenerService()) && false ?: '_'});
        $d->addEventSubscriber($c);
        $d->addEventListener(array(0 => 'postPersist', 1 => 'preUpdate'), ${($_ = isset($this->services['doctrine.listener']) ? $this->services['doctrine.listener'] : $this->get('doctrine.listener')) && false ?: '_'});
        $d->addEventListener(array(0 => 'loadClassMetadata'), ${($_ = isset($this->services['doctrine.orm.default_listeners.attach_entity_listeners']) ? $this->services['doctrine.orm.default_listeners.attach_entity_listeners'] : $this->get('doctrine.orm.default_listeners.attach_entity_listeners')) && false ?: '_'});

        return $this->services['doctrine.dbal.default_connection'] = ${($_ = isset($this->services['doctrine.dbal.connection_factory']) ? $this->services['doctrine.dbal.connection_factory'] : $this->get('doctrine.dbal.connection_factory')) && false ?: '_'}->createConnection(array('driver' => 'pdo_mysql', 'host' => 'localhost', 'port' => NULL, 'dbname' => 'ScanOrder', 'user' => 'symfony2', 'password' => 'symfony2', 'charset' => 'UTF8', 'driverOptions' => array(), 'defaultTableOptions' => array()), $b, $d, array());
    }

    /**
     * Gets the public 'doctrine.listener' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Services\DoctrineListener
     */
    protected function getDoctrine_ListenerService()
    {
        return $this->services['doctrine.listener'] = new \Oleg\UserdirectoryBundle\Services\DoctrineListener($this);
    }

    /**
     * Gets the public 'doctrine.orm.aperio_entity_listener_resolver' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerAwareEntityListenerResolver
     */
    protected function getDoctrine_Orm_AperioEntityListenerResolverService()
    {
        return $this->services['doctrine.orm.aperio_entity_listener_resolver'] = new \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerAwareEntityListenerResolver($this);
    }

    /**
     * Gets the public 'doctrine.orm.aperio_entity_manager' shared service.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getDoctrine_Orm_AperioEntityManagerService($lazyLoad = true)
    {
        $a = new \Doctrine\ORM\Configuration();
        $a->setEntityNamespaces(array());
        $a->setMetadataCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.aperio_metadata_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.aperio_metadata_cache'] : $this->get('doctrine_cache.providers.doctrine.orm.aperio_metadata_cache')) && false ?: '_'});
        $a->setQueryCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.aperio_query_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.aperio_query_cache'] : $this->get('doctrine_cache.providers.doctrine.orm.aperio_query_cache')) && false ?: '_'});
        $a->setResultCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.aperio_result_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.aperio_result_cache'] : $this->get('doctrine_cache.providers.doctrine.orm.aperio_result_cache')) && false ?: '_'});
        $a->setMetadataDriverImpl(new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain());
        $a->setProxyDir((__DIR__.'/doctrine/orm/Proxies'));
        $a->setProxyNamespace('Proxies');
        $a->setAutoGenerateProxyClasses(true);
        $a->setClassMetadataFactoryName('Doctrine\\ORM\\Mapping\\ClassMetadataFactory');
        $a->setDefaultRepositoryClassName('Doctrine\\ORM\\EntityRepository');
        $a->setNamingStrategy(${($_ = isset($this->services['doctrine.orm.naming_strategy.default']) ? $this->services['doctrine.orm.naming_strategy.default'] : $this->getDoctrine_Orm_NamingStrategy_DefaultService()) && false ?: '_'});
        $a->setQuoteStrategy(${($_ = isset($this->services['doctrine.orm.quote_strategy.default']) ? $this->services['doctrine.orm.quote_strategy.default'] : $this->getDoctrine_Orm_QuoteStrategy_DefaultService()) && false ?: '_'});
        $a->setEntityListenerResolver(${($_ = isset($this->services['doctrine.orm.aperio_entity_listener_resolver']) ? $this->services['doctrine.orm.aperio_entity_listener_resolver'] : $this->get('doctrine.orm.aperio_entity_listener_resolver')) && false ?: '_'});

        $this->services['doctrine.orm.aperio_entity_manager'] = $instance = \Doctrine\ORM\EntityManager::create(${($_ = isset($this->services['doctrine.dbal.aperio_connection']) ? $this->services['doctrine.dbal.aperio_connection'] : $this->get('doctrine.dbal.aperio_connection')) && false ?: '_'}, $a);

        ${($_ = isset($this->services['doctrine.orm.aperio_manager_configurator']) ? $this->services['doctrine.orm.aperio_manager_configurator'] : $this->get('doctrine.orm.aperio_manager_configurator')) && false ?: '_'}->configure($instance);

        return $instance;
    }

    /**
     * Gets the public 'doctrine.orm.aperio_entity_manager.property_info_extractor' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor
     */
    protected function getDoctrine_Orm_AperioEntityManager_PropertyInfoExtractorService()
    {
        return $this->services['doctrine.orm.aperio_entity_manager.property_info_extractor'] = new \Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor(${($_ = isset($this->services['doctrine.orm.aperio_entity_manager']) ? $this->services['doctrine.orm.aperio_entity_manager'] : $this->get('doctrine.orm.aperio_entity_manager')) && false ?: '_'}->getMetadataFactory());
    }

    /**
     * Gets the public 'doctrine.orm.aperio_listeners.attach_entity_listeners' shared service.
     *
     * @return \Doctrine\ORM\Tools\AttachEntityListenersListener
     */
    protected function getDoctrine_Orm_AperioListeners_AttachEntityListenersService()
    {
        return $this->services['doctrine.orm.aperio_listeners.attach_entity_listeners'] = new \Doctrine\ORM\Tools\AttachEntityListenersListener();
    }

    /**
     * Gets the public 'doctrine.orm.aperio_manager_configurator' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator
     */
    protected function getDoctrine_Orm_AperioManagerConfiguratorService()
    {
        return $this->services['doctrine.orm.aperio_manager_configurator'] = new \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator(array(), array());
    }

    /**
     * Gets the public 'doctrine.orm.default_entity_listener_resolver' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerAwareEntityListenerResolver
     */
    protected function getDoctrine_Orm_DefaultEntityListenerResolverService()
    {
        return $this->services['doctrine.orm.default_entity_listener_resolver'] = new \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerAwareEntityListenerResolver($this);
    }

    /**
     * Gets the public 'doctrine.orm.default_entity_manager' shared service.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getDoctrine_Orm_DefaultEntityManagerService($lazyLoad = true)
    {
        $a = ${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->get('annotation_reader')) && false ?: '_'};

        $b = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($a, array(0 => ($this->targetDirs[3].'\\src\\Oleg\\OrderformBundle\\Entity'), 1 => ($this->targetDirs[3].'\\src\\Oleg\\UserdirectoryBundle\\Entity'), 2 => ($this->targetDirs[3].'\\src\\Oleg\\FellAppBundle\\Entity'), 3 => ($this->targetDirs[3].'\\src\\Oleg\\VacReqBundle\\Entity'), 4 => ($this->targetDirs[3].'\\src\\Oleg\\CallLogBundle\\Entity'), 5 => ($this->targetDirs[3].'\\src\\Oleg\\TranslationalResearchBundle\\Entity'), 6 => ($this->targetDirs[3].'\\vendor\\gedmo\\doctrine-extensions\\lib\\Gedmo\\Tree\\Entity')));

        $c = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
        $c->addDriver($b, 'Oleg\\OrderformBundle\\Entity');
        $c->addDriver($b, 'Oleg\\UserdirectoryBundle\\Entity');
        $c->addDriver($b, 'Oleg\\FellAppBundle\\Entity');
        $c->addDriver($b, 'Oleg\\VacReqBundle\\Entity');
        $c->addDriver($b, 'Oleg\\CallLogBundle\\Entity');
        $c->addDriver($b, 'Oleg\\TranslationalResearchBundle\\Entity');
        $c->addDriver($b, 'Gedmo\\Tree\\Entity');
        $c->addDriver(new \Doctrine\ORM\Mapping\Driver\XmlDriver(new \Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator(array(($this->targetDirs[3].'\\vendor\\friendsofsymfony\\user-bundle\\Resources\\config\\doctrine-mapping') => 'FOS\\UserBundle\\Model'), '.orm.xml')), 'FOS\\UserBundle\\Model');

        $d = new \Doctrine\ORM\Configuration();
        $d->setEntityNamespaces(array('OlegOrderformBundle' => 'Oleg\\OrderformBundle\\Entity', 'OlegUserdirectoryBundle' => 'Oleg\\UserdirectoryBundle\\Entity', 'OlegFellAppBundle' => 'Oleg\\FellAppBundle\\Entity', 'OlegVacReqBundle' => 'Oleg\\VacReqBundle\\Entity', 'OlegCallLogBundle' => 'Oleg\\CallLogBundle\\Entity', 'OlegTranslationalResearchBundle' => 'Oleg\\TranslationalResearchBundle\\Entity', 'GedmoTree' => 'Gedmo\\Tree\\Entity'));
        $d->setMetadataCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.default_metadata_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.default_metadata_cache'] : $this->get('doctrine_cache.providers.doctrine.orm.default_metadata_cache')) && false ?: '_'});
        $d->setQueryCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.default_query_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.default_query_cache'] : $this->get('doctrine_cache.providers.doctrine.orm.default_query_cache')) && false ?: '_'});
        $d->setResultCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.default_result_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.default_result_cache'] : $this->get('doctrine_cache.providers.doctrine.orm.default_result_cache')) && false ?: '_'});
        $d->setMetadataDriverImpl($c);
        $d->setProxyDir((__DIR__.'/doctrine/orm/Proxies'));
        $d->setProxyNamespace('Proxies');
        $d->setAutoGenerateProxyClasses(true);
        $d->setClassMetadataFactoryName('Doctrine\\ORM\\Mapping\\ClassMetadataFactory');
        $d->setDefaultRepositoryClassName('Doctrine\\ORM\\EntityRepository');
        $d->setNamingStrategy(${($_ = isset($this->services['doctrine.orm.naming_strategy.default']) ? $this->services['doctrine.orm.naming_strategy.default'] : $this->getDoctrine_Orm_NamingStrategy_DefaultService()) && false ?: '_'});
        $d->setQuoteStrategy(${($_ = isset($this->services['doctrine.orm.quote_strategy.default']) ? $this->services['doctrine.orm.quote_strategy.default'] : $this->getDoctrine_Orm_QuoteStrategy_DefaultService()) && false ?: '_'});
        $d->setEntityListenerResolver(${($_ = isset($this->services['doctrine.orm.default_entity_listener_resolver']) ? $this->services['doctrine.orm.default_entity_listener_resolver'] : $this->get('doctrine.orm.default_entity_listener_resolver')) && false ?: '_'});
        $d->addCustomHydrationMode('SimpleHydrator', '\\Oleg\\UserdirectoryBundle\\Hydrator\\SimpleHydrator');
        $d->addCustomHydrationMode('StainHydrator', '\\Oleg\\OrderformBundle\\Hydrator\\StainHydrator');
        $d->addCustomStringFunction('CAST', 'Oleg\\UserdirectoryBundle\\Query\\CastFunction');

        $this->services['doctrine.orm.default_entity_manager'] = $instance = \Doctrine\ORM\EntityManager::create(${($_ = isset($this->services['doctrine.dbal.default_connection']) ? $this->services['doctrine.dbal.default_connection'] : $this->get('doctrine.dbal.default_connection')) && false ?: '_'}, $d);

        ${($_ = isset($this->services['doctrine.orm.default_manager_configurator']) ? $this->services['doctrine.orm.default_manager_configurator'] : $this->get('doctrine.orm.default_manager_configurator')) && false ?: '_'}->configure($instance);

        return $instance;
    }

    /**
     * Gets the public 'doctrine.orm.default_entity_manager.property_info_extractor' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor
     */
    protected function getDoctrine_Orm_DefaultEntityManager_PropertyInfoExtractorService()
    {
        return $this->services['doctrine.orm.default_entity_manager.property_info_extractor'] = new \Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}->getMetadataFactory());
    }

    /**
     * Gets the public 'doctrine.orm.default_listeners.attach_entity_listeners' shared service.
     *
     * @return \Doctrine\ORM\Tools\AttachEntityListenersListener
     */
    protected function getDoctrine_Orm_DefaultListeners_AttachEntityListenersService()
    {
        return $this->services['doctrine.orm.default_listeners.attach_entity_listeners'] = new \Doctrine\ORM\Tools\AttachEntityListenersListener();
    }

    /**
     * Gets the public 'doctrine.orm.default_manager_configurator' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator
     */
    protected function getDoctrine_Orm_DefaultManagerConfiguratorService()
    {
        return $this->services['doctrine.orm.default_manager_configurator'] = new \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator(array(), array());
    }

    /**
     * Gets the public 'doctrine.orm.validator.unique' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator
     */
    protected function getDoctrine_Orm_Validator_UniqueService()
    {
        return $this->services['doctrine.orm.validator.unique'] = new \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'});
    }

    /**
     * Gets the public 'doctrine.orm.validator_initializer' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\Validator\DoctrineInitializer
     */
    protected function getDoctrine_Orm_ValidatorInitializerService()
    {
        return $this->services['doctrine.orm.validator_initializer'] = new \Symfony\Bridge\Doctrine\Validator\DoctrineInitializer(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'});
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.aperio_metadata_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_AperioMetadataCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.aperio_metadata_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf2orm_aperio_fd4cc440ef721277fd77a7906561fe2a337501f96e2ea760317e6a141676d58e');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.aperio_query_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_AperioQueryCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.aperio_query_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf2orm_aperio_fd4cc440ef721277fd77a7906561fe2a337501f96e2ea760317e6a141676d58e');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.aperio_result_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_AperioResultCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.aperio_result_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf2orm_aperio_fd4cc440ef721277fd77a7906561fe2a337501f96e2ea760317e6a141676d58e');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.default_metadata_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_DefaultMetadataCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.default_metadata_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf2orm_default_fd4cc440ef721277fd77a7906561fe2a337501f96e2ea760317e6a141676d58e');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.default_query_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_DefaultQueryCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.default_query_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf2orm_default_fd4cc440ef721277fd77a7906561fe2a337501f96e2ea760317e6a141676d58e');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.default_result_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_DefaultResultCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.default_result_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf2orm_default_fd4cc440ef721277fd77a7906561fe2a337501f96e2ea760317e6a141676d58e');

        return $instance;
    }

    /**
     * Gets the public 'employees_authentication_handler' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler
     */
    protected function getEmployeesAuthenticationHandlerService()
    {
        return $this->services['employees_authentication_handler'] = new \Oleg\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'fellapp_authentication_handler' shared service.
     *
     * @return \Oleg\FellAppBundle\Security\Authentication\FellAppLoginSuccessHandler
     */
    protected function getFellappAuthenticationHandlerService()
    {
        return $this->services['fellapp_authentication_handler'] = new \Oleg\FellAppBundle\Security\Authentication\FellAppLoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'fellapp_googlesheetmanagement' shared service.
     *
     * @return \Oleg\FellAppBundle\Util\GoogleSheetManagement
     */
    protected function getFellappGooglesheetmanagementService()
    {
        return $this->services['fellapp_googlesheetmanagement'] = new \Oleg\FellAppBundle\Util\GoogleSheetManagement(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'fellapp_importpopulate_util' shared service.
     *
     * @return \Oleg\FellAppBundle\Util\FellAppImportPopulateUtil
     */
    protected function getFellappImportpopulateUtilService()
    {
        return $this->services['fellapp_importpopulate_util'] = new \Oleg\FellAppBundle\Util\FellAppImportPopulateUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'fellapp_reportgenerator' shared service.
     *
     * @return \Oleg\FellAppBundle\Util\ReportGenerator
     */
    protected function getFellappReportgeneratorService()
    {
        return $this->services['fellapp_reportgenerator'] = new \Oleg\FellAppBundle\Util\ReportGenerator(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this, ${($_ = isset($this->services['templating']) ? $this->services['templating'] : $this->get('templating')) && false ?: '_'});
    }

    /**
     * Gets the public 'fellapp_util' shared service.
     *
     * @return \Oleg\FellAppBundle\Util\FellAppUtil
     */
    protected function getFellappUtilService()
    {
        return $this->services['fellapp_util'] = new \Oleg\FellAppBundle\Util\FellAppUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'file_locator' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocatorService()
    {
        return $this->services['file_locator'] = new \Symfony\Component\HttpKernel\Config\FileLocator(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'}, ($this->targetDirs[3].'\\app/Resources'), array(0 => ($this->targetDirs[3].'\\app')));
    }

    /**
     * Gets the public 'filesystem' shared service.
     *
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    protected function getFilesystemService()
    {
        return $this->services['filesystem'] = new \Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * Gets the public 'form.factory' shared service.
     *
     * @return \Symfony\Component\Form\FormFactory
     */
    protected function getForm_FactoryService()
    {
        return $this->services['form.factory'] = new \Symfony\Component\Form\FormFactory(${($_ = isset($this->services['form.registry']) ? $this->services['form.registry'] : $this->get('form.registry')) && false ?: '_'}, ${($_ = isset($this->services['form.resolved_type_factory']) ? $this->services['form.resolved_type_factory'] : $this->get('form.resolved_type_factory')) && false ?: '_'});
    }

    /**
     * Gets the public 'form.registry' shared service.
     *
     * @return \Symfony\Component\Form\FormRegistry
     */
    protected function getForm_RegistryService()
    {
        return $this->services['form.registry'] = new \Symfony\Component\Form\FormRegistry(array(0 => new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension(new \Symfony\Component\DependencyInjection\ServiceLocator(array('FOS\\UserBundle\\Form\\Type\\ChangePasswordFormType' => function () {
            return ${($_ = isset($this->services['fos_user.change_password.form.type']) ? $this->services['fos_user.change_password.form.type'] : $this->get('fos_user.change_password.form.type')) && false ?: '_'};
        }, 'FOS\\UserBundle\\Form\\Type\\ProfileFormType' => function () {
            return ${($_ = isset($this->services['fos_user.profile.form.type']) ? $this->services['fos_user.profile.form.type'] : $this->get('fos_user.profile.form.type')) && false ?: '_'};
        }, 'FOS\\UserBundle\\Form\\Type\\RegistrationFormType' => function () {
            return ${($_ = isset($this->services['fos_user.registration.form.type']) ? $this->services['fos_user.registration.form.type'] : $this->get('fos_user.registration.form.type')) && false ?: '_'};
        }, 'FOS\\UserBundle\\Form\\Type\\ResettingFormType' => function () {
            return ${($_ = isset($this->services['fos_user.resetting.form.type']) ? $this->services['fos_user.resetting.form.type'] : $this->get('fos_user.resetting.form.type')) && false ?: '_'};
        }, 'FOS\\UserBundle\\Form\\Type\\UsernameFormType' => function () {
            return ${($_ = isset($this->services['fos_user.username_form_type']) ? $this->services['fos_user.username_form_type'] : $this->get('fos_user.username_form_type')) && false ?: '_'};
        }, 'Oleg\\OrderformBundle\\Form\\CustomType\\ScanCustomSelectorType' => function () {
            return ${($_ = isset($this->services['order_form.type.scan_custom_selector']) ? $this->services['order_form.type.scan_custom_selector'] : $this->get('order_form.type.scan_custom_selector')) && false ?: '_'};
        }, 'Oleg\\UserdirectoryBundle\\Form\\CustomType\\CustomSelectorType' => function () {
            return ${($_ = isset($this->services['oleg.type.employees_custom_selector']) ? $this->services['oleg.type.employees_custom_selector'] : $this->get('oleg.type.employees_custom_selector')) && false ?: '_'};
        }, 'Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType' => function () {
            return ${($_ = isset($this->services['form.type.entity']) ? $this->services['form.type.entity'] : $this->get('form.type.entity')) && false ?: '_'};
        }, 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType' => function () {
            return ${($_ = isset($this->services['form.type.choice']) ? $this->services['form.type.choice'] : $this->getForm_Type_ChoiceService()) && false ?: '_'};
        }, 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => function () {
            return ${($_ = isset($this->services['form.type.form']) ? $this->services['form.type.form'] : $this->getForm_Type_FormService()) && false ?: '_'};
        })), array('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_extension.form.http_foundation']) ? $this->services['form.type_extension.form.http_foundation'] : $this->getForm_TypeExtension_Form_HttpFoundationService()) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['form.type_extension.form.validator']) ? $this->services['form.type_extension.form.validator'] : $this->getForm_TypeExtension_Form_ValidatorService()) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['form.type_extension.upload.validator']) ? $this->services['form.type_extension.upload.validator'] : $this->getForm_TypeExtension_Upload_ValidatorService()) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['form.type_extension.csrf']) ? $this->services['form.type_extension.csrf'] : $this->getForm_TypeExtension_CsrfService()) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['form.type_extension.form.data_collector']) ? $this->services['form.type_extension.form.data_collector'] : $this->getForm_TypeExtension_Form_DataCollectorService()) && false ?: '_'};
        }, 5), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_extension.repeated.validator']) ? $this->services['form.type_extension.repeated.validator'] : $this->getForm_TypeExtension_Repeated_ValidatorService()) && false ?: '_'};
        }, 1), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_extension.submit.validator']) ? $this->services['form.type_extension.submit.validator'] : $this->getForm_TypeExtension_Submit_ValidatorService()) && false ?: '_'};
        }, 1)), new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_guesser.validator']) ? $this->services['form.type_guesser.validator'] : $this->getForm_TypeGuesser_ValidatorService()) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['form.type_guesser.doctrine']) ? $this->services['form.type_guesser.doctrine'] : $this->get('form.type_guesser.doctrine')) && false ?: '_'};
        }, 2), NULL)), ${($_ = isset($this->services['form.resolved_type_factory']) ? $this->services['form.resolved_type_factory'] : $this->get('form.resolved_type_factory')) && false ?: '_'});
    }

    /**
     * Gets the public 'form.resolved_type_factory' shared service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeFactoryDataCollectorProxy
     */
    protected function getForm_ResolvedTypeFactoryService()
    {
        return $this->services['form.resolved_type_factory'] = new \Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeFactoryDataCollectorProxy(new \Symfony\Component\Form\ResolvedFormTypeFactory(), ${($_ = isset($this->services['data_collector.form']) ? $this->services['data_collector.form'] : $this->get('data_collector.form')) && false ?: '_'});
    }

    /**
     * Gets the public 'form.type.birthday' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\BirthdayType
     *
     * @deprecated The "form.type.birthday" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_BirthdayService()
    {
        @trigger_error('The "form.type.birthday" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.birthday'] = new \Symfony\Component\Form\Extension\Core\Type\BirthdayType();
    }

    /**
     * Gets the public 'form.type.button' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\ButtonType
     *
     * @deprecated The "form.type.button" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_ButtonService()
    {
        @trigger_error('The "form.type.button" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.button'] = new \Symfony\Component\Form\Extension\Core\Type\ButtonType();
    }

    /**
     * Gets the public 'form.type.checkbox' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CheckboxType
     *
     * @deprecated The "form.type.checkbox" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_CheckboxService()
    {
        @trigger_error('The "form.type.checkbox" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.checkbox'] = new \Symfony\Component\Form\Extension\Core\Type\CheckboxType();
    }

    /**
     * Gets the public 'form.type.collection' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CollectionType
     *
     * @deprecated The "form.type.collection" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_CollectionService()
    {
        @trigger_error('The "form.type.collection" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.collection'] = new \Symfony\Component\Form\Extension\Core\Type\CollectionType();
    }

    /**
     * Gets the public 'form.type.country' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CountryType
     *
     * @deprecated The "form.type.country" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_CountryService()
    {
        @trigger_error('The "form.type.country" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.country'] = new \Symfony\Component\Form\Extension\Core\Type\CountryType();
    }

    /**
     * Gets the public 'form.type.currency' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CurrencyType
     *
     * @deprecated The "form.type.currency" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_CurrencyService()
    {
        @trigger_error('The "form.type.currency" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.currency'] = new \Symfony\Component\Form\Extension\Core\Type\CurrencyType();
    }

    /**
     * Gets the public 'form.type.date' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\DateType
     *
     * @deprecated The "form.type.date" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_DateService()
    {
        @trigger_error('The "form.type.date" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.date'] = new \Symfony\Component\Form\Extension\Core\Type\DateType();
    }

    /**
     * Gets the public 'form.type.datetime' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\DateTimeType
     *
     * @deprecated The "form.type.datetime" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_DatetimeService()
    {
        @trigger_error('The "form.type.datetime" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.datetime'] = new \Symfony\Component\Form\Extension\Core\Type\DateTimeType();
    }

    /**
     * Gets the public 'form.type.email' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\EmailType
     *
     * @deprecated The "form.type.email" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_EmailService()
    {
        @trigger_error('The "form.type.email" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.email'] = new \Symfony\Component\Form\Extension\Core\Type\EmailType();
    }

    /**
     * Gets the public 'form.type.entity' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\Form\Type\EntityType
     */
    protected function getForm_Type_EntityService()
    {
        return $this->services['form.type.entity'] = new \Symfony\Bridge\Doctrine\Form\Type\EntityType(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'});
    }

    /**
     * Gets the public 'form.type.file' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\FileType
     *
     * @deprecated The "form.type.file" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_FileService()
    {
        @trigger_error('The "form.type.file" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.file'] = new \Symfony\Component\Form\Extension\Core\Type\FileType();
    }

    /**
     * Gets the public 'form.type.hidden' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\HiddenType
     *
     * @deprecated The "form.type.hidden" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_HiddenService()
    {
        @trigger_error('The "form.type.hidden" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.hidden'] = new \Symfony\Component\Form\Extension\Core\Type\HiddenType();
    }

    /**
     * Gets the public 'form.type.integer' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\IntegerType
     *
     * @deprecated The "form.type.integer" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_IntegerService()
    {
        @trigger_error('The "form.type.integer" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.integer'] = new \Symfony\Component\Form\Extension\Core\Type\IntegerType();
    }

    /**
     * Gets the public 'form.type.language' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\LanguageType
     *
     * @deprecated The "form.type.language" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_LanguageService()
    {
        @trigger_error('The "form.type.language" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.language'] = new \Symfony\Component\Form\Extension\Core\Type\LanguageType();
    }

    /**
     * Gets the public 'form.type.locale' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\LocaleType
     *
     * @deprecated The "form.type.locale" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_LocaleService()
    {
        @trigger_error('The "form.type.locale" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.locale'] = new \Symfony\Component\Form\Extension\Core\Type\LocaleType();
    }

    /**
     * Gets the public 'form.type.money' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\MoneyType
     *
     * @deprecated The "form.type.money" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_MoneyService()
    {
        @trigger_error('The "form.type.money" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.money'] = new \Symfony\Component\Form\Extension\Core\Type\MoneyType();
    }

    /**
     * Gets the public 'form.type.number' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\NumberType
     *
     * @deprecated The "form.type.number" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_NumberService()
    {
        @trigger_error('The "form.type.number" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.number'] = new \Symfony\Component\Form\Extension\Core\Type\NumberType();
    }

    /**
     * Gets the public 'form.type.password' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\PasswordType
     *
     * @deprecated The "form.type.password" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_PasswordService()
    {
        @trigger_error('The "form.type.password" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.password'] = new \Symfony\Component\Form\Extension\Core\Type\PasswordType();
    }

    /**
     * Gets the public 'form.type.percent' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\PercentType
     *
     * @deprecated The "form.type.percent" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_PercentService()
    {
        @trigger_error('The "form.type.percent" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.percent'] = new \Symfony\Component\Form\Extension\Core\Type\PercentType();
    }

    /**
     * Gets the public 'form.type.radio' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\RadioType
     *
     * @deprecated The "form.type.radio" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_RadioService()
    {
        @trigger_error('The "form.type.radio" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.radio'] = new \Symfony\Component\Form\Extension\Core\Type\RadioType();
    }

    /**
     * Gets the public 'form.type.range' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\RangeType
     *
     * @deprecated The "form.type.range" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_RangeService()
    {
        @trigger_error('The "form.type.range" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.range'] = new \Symfony\Component\Form\Extension\Core\Type\RangeType();
    }

    /**
     * Gets the public 'form.type.repeated' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\RepeatedType
     *
     * @deprecated The "form.type.repeated" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_RepeatedService()
    {
        @trigger_error('The "form.type.repeated" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.repeated'] = new \Symfony\Component\Form\Extension\Core\Type\RepeatedType();
    }

    /**
     * Gets the public 'form.type.reset' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\ResetType
     *
     * @deprecated The "form.type.reset" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_ResetService()
    {
        @trigger_error('The "form.type.reset" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.reset'] = new \Symfony\Component\Form\Extension\Core\Type\ResetType();
    }

    /**
     * Gets the public 'form.type.search' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\SearchType
     *
     * @deprecated The "form.type.search" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_SearchService()
    {
        @trigger_error('The "form.type.search" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.search'] = new \Symfony\Component\Form\Extension\Core\Type\SearchType();
    }

    /**
     * Gets the public 'form.type.submit' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\SubmitType
     *
     * @deprecated The "form.type.submit" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_SubmitService()
    {
        @trigger_error('The "form.type.submit" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.submit'] = new \Symfony\Component\Form\Extension\Core\Type\SubmitType();
    }

    /**
     * Gets the public 'form.type.text' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TextType
     *
     * @deprecated The "form.type.text" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_TextService()
    {
        @trigger_error('The "form.type.text" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.text'] = new \Symfony\Component\Form\Extension\Core\Type\TextType();
    }

    /**
     * Gets the public 'form.type.textarea' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TextareaType
     *
     * @deprecated The "form.type.textarea" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_TextareaService()
    {
        @trigger_error('The "form.type.textarea" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.textarea'] = new \Symfony\Component\Form\Extension\Core\Type\TextareaType();
    }

    /**
     * Gets the public 'form.type.time' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TimeType
     *
     * @deprecated The "form.type.time" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_TimeService()
    {
        @trigger_error('The "form.type.time" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.time'] = new \Symfony\Component\Form\Extension\Core\Type\TimeType();
    }

    /**
     * Gets the public 'form.type.timezone' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TimezoneType
     *
     * @deprecated The "form.type.timezone" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_TimezoneService()
    {
        @trigger_error('The "form.type.timezone" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.timezone'] = new \Symfony\Component\Form\Extension\Core\Type\TimezoneType();
    }

    /**
     * Gets the public 'form.type.url' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\UrlType
     *
     * @deprecated The "form.type.url" service is deprecated since Symfony 3.1 and will be removed in 4.0.
     */
    protected function getForm_Type_UrlService()
    {
        @trigger_error('The "form.type.url" service is deprecated since Symfony 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->services['form.type.url'] = new \Symfony\Component\Form\Extension\Core\Type\UrlType();
    }

    /**
     * Gets the public 'form.type_guesser.doctrine' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser
     */
    protected function getForm_TypeGuesser_DoctrineService()
    {
        return $this->services['form.type_guesser.doctrine'] = new \Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'});
    }

    /**
     * Gets the public 'fos_js_routing.controller' shared service.
     *
     * @return \FOS\JsRoutingBundle\Controller\Controller
     */
    protected function getFosJsRouting_ControllerService()
    {
        return $this->services['fos_js_routing.controller'] = new \FOS\JsRoutingBundle\Controller\Controller(${($_ = isset($this->services['fos_js_routing.serializer']) ? $this->services['fos_js_routing.serializer'] : $this->get('fos_js_routing.serializer')) && false ?: '_'}, ${($_ = isset($this->services['fos_js_routing.extractor']) ? $this->services['fos_js_routing.extractor'] : $this->get('fos_js_routing.extractor')) && false ?: '_'}, array('enabled' => false), true);
    }

    /**
     * Gets the public 'fos_js_routing.extractor' shared service.
     *
     * @return \FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor
     */
    protected function getFosJsRouting_ExtractorService()
    {
        return $this->services['fos_js_routing.extractor'] = new \FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, array(), __DIR__, array('FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle', 'SecurityBundle' => 'Symfony\\Bundle\\SecurityBundle\\SecurityBundle', 'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle', 'MonologBundle' => 'Symfony\\Bundle\\MonologBundle\\MonologBundle', 'SwiftmailerBundle' => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle', 'AsseticBundle' => 'Symfony\\Bundle\\AsseticBundle\\AsseticBundle', 'DoctrineBundle' => 'Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle', 'SensioFrameworkExtraBundle' => 'Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle', 'KnpPaginatorBundle' => 'Knp\\Bundle\\PaginatorBundle\\KnpPaginatorBundle', 'FOSUserBundle' => 'FOS\\UserBundle\\FOSUserBundle', 'OneupUploaderBundle' => 'Oneup\\UploaderBundle\\OneupUploaderBundle', 'FOSJsRoutingBundle' => 'FOS\\JsRoutingBundle\\FOSJsRoutingBundle', 'StofDoctrineExtensionsBundle' => 'Stof\\DoctrineExtensionsBundle\\StofDoctrineExtensionsBundle', 'EnseparHtml2pdfBundle' => 'Ensepar\\Html2pdfBundle\\EnseparHtml2pdfBundle', 'SpraedPDFGeneratorBundle' => 'Spraed\\PDFGeneratorBundle\\SpraedPDFGeneratorBundle', 'KnpSnappyBundle' => 'Knp\\Bundle\\SnappyBundle\\KnpSnappyBundle', 'ADesignsCalendarBundle' => 'ADesigns\\CalendarBundle\\ADesignsCalendarBundle', 'BmatznerFontAwesomeBundle' => 'Bmatzner\\FontAwesomeBundle\\BmatznerFontAwesomeBundle', 'OlegUserdirectoryBundle' => 'Oleg\\UserdirectoryBundle\\OlegUserdirectoryBundle', 'OlegOrderformBundle' => 'Oleg\\OrderformBundle\\OlegOrderformBundle', 'OlegFellAppBundle' => 'Oleg\\FellAppBundle\\OlegFellAppBundle', 'OlegDeidentifierBundle' => 'Oleg\\DeidentifierBundle\\OlegDeidentifierBundle', 'OlegVacReqBundle' => 'Oleg\\VacReqBundle\\OlegVacReqBundle', 'OlegCallLogBundle' => 'Oleg\\CallLogBundle\\OlegCallLogBundle', 'OlegTranslationalResearchBundle' => 'Oleg\\TranslationalResearchBundle\\OlegTranslationalResearchBundle', 'DebugBundle' => 'Symfony\\Bundle\\DebugBundle\\DebugBundle', 'WebProfilerBundle' => 'Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle', 'SensioDistributionBundle' => 'Sensio\\Bundle\\DistributionBundle\\SensioDistributionBundle', 'WebServerBundle' => 'Symfony\\Bundle\\WebServerBundle\\WebServerBundle', 'SensioGeneratorBundle' => 'Sensio\\Bundle\\GeneratorBundle\\SensioGeneratorBundle'));
    }

    /**
     * Gets the public 'fos_js_routing.serializer' shared service.
     *
     * @return \Symfony\Component\Serializer\Serializer
     */
    protected function getFosJsRouting_SerializerService()
    {
        return $this->services['fos_js_routing.serializer'] = new \Symfony\Component\Serializer\Serializer(array(0 => new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer()), array('json' => new \Symfony\Component\Serializer\Encoder\JsonEncoder()));
    }

    /**
     * Gets the public 'fos_user.change_password.form.factory' shared service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory
     */
    protected function getFosUser_ChangePassword_Form_FactoryService()
    {
        return $this->services['fos_user.change_password.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory(${($_ = isset($this->services['form.factory']) ? $this->services['form.factory'] : $this->get('form.factory')) && false ?: '_'}, 'fos_user_change_password_form', 'FOS\\UserBundle\\Form\\Type\\ChangePasswordFormType', array(0 => 'ChangePassword', 1 => 'Default'));
    }

    /**
     * Gets the public 'fos_user.change_password.form.type' shared service.
     *
     * @return \FOS\UserBundle\Form\Type\ChangePasswordFormType
     */
    protected function getFosUser_ChangePassword_Form_TypeService()
    {
        return $this->services['fos_user.change_password.form.type'] = new \FOS\UserBundle\Form\Type\ChangePasswordFormType('Oleg\\UserdirectoryBundle\\Entity\\User');
    }

    /**
     * Gets the public 'fos_user.listener.authentication' shared service.
     *
     * @return \FOS\UserBundle\EventListener\AuthenticationListener
     */
    protected function getFosUser_Listener_AuthenticationService()
    {
        return $this->services['fos_user.listener.authentication'] = new \FOS\UserBundle\EventListener\AuthenticationListener(${($_ = isset($this->services['fos_user.security.login_manager']) ? $this->services['fos_user.security.login_manager'] : $this->get('fos_user.security.login_manager')) && false ?: '_'}, 'main');
    }

    /**
     * Gets the public 'fos_user.listener.flash' shared service.
     *
     * @return \FOS\UserBundle\EventListener\FlashListener
     */
    protected function getFosUser_Listener_FlashService()
    {
        return $this->services['fos_user.listener.flash'] = new \FOS\UserBundle\EventListener\FlashListener(${($_ = isset($this->services['session']) ? $this->services['session'] : $this->get('session')) && false ?: '_'}, ${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->get('translator')) && false ?: '_'});
    }

    /**
     * Gets the public 'fos_user.listener.resetting' shared service.
     *
     * @return \FOS\UserBundle\EventListener\ResettingListener
     */
    protected function getFosUser_Listener_ResettingService()
    {
        return $this->services['fos_user.listener.resetting'] = new \FOS\UserBundle\EventListener\ResettingListener(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, 86400);
    }

    /**
     * Gets the public 'fos_user.mailer' shared service.
     *
     * @return \FOS\UserBundle\Mailer\Mailer
     */
    protected function getFosUser_MailerService()
    {
        return $this->services['fos_user.mailer'] = new \FOS\UserBundle\Mailer\Mailer(${($_ = isset($this->services['swiftmailer.mailer.default']) ? $this->services['swiftmailer.mailer.default'] : $this->get('swiftmailer.mailer.default')) && false ?: '_'}, ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, ${($_ = isset($this->services['templating']) ? $this->services['templating'] : $this->get('templating')) && false ?: '_'}, array('confirmation.template' => '@FOSUser/Registration/email.txt.twig', 'resetting.template' => '@FOSUser/Resetting/email.txt.twig', 'from_email' => array('confirmation' => array('oli2002@med.cornell.edu' => 'Oleg Ivanov'), 'resetting' => array('oli2002@med.cornell.edu' => 'Oleg Ivanov'))));
    }

    /**
     * Gets the public 'fos_user.profile.form.factory' shared service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory
     */
    protected function getFosUser_Profile_Form_FactoryService()
    {
        return $this->services['fos_user.profile.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory(${($_ = isset($this->services['form.factory']) ? $this->services['form.factory'] : $this->get('form.factory')) && false ?: '_'}, 'fos_user_profile_form', 'FOS\\UserBundle\\Form\\Type\\ProfileFormType', array(0 => 'Profile', 1 => 'Default'));
    }

    /**
     * Gets the public 'fos_user.profile.form.type' shared service.
     *
     * @return \FOS\UserBundle\Form\Type\ProfileFormType
     */
    protected function getFosUser_Profile_Form_TypeService()
    {
        return $this->services['fos_user.profile.form.type'] = new \FOS\UserBundle\Form\Type\ProfileFormType('Oleg\\UserdirectoryBundle\\Entity\\User');
    }

    /**
     * Gets the public 'fos_user.registration.form.factory' shared service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory
     */
    protected function getFosUser_Registration_Form_FactoryService()
    {
        return $this->services['fos_user.registration.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory(${($_ = isset($this->services['form.factory']) ? $this->services['form.factory'] : $this->get('form.factory')) && false ?: '_'}, 'fos_user_registration_form', 'FOS\\UserBundle\\Form\\Type\\RegistrationFormType', array(0 => 'Registration', 1 => 'Default'));
    }

    /**
     * Gets the public 'fos_user.registration.form.type' shared service.
     *
     * @return \FOS\UserBundle\Form\Type\RegistrationFormType
     */
    protected function getFosUser_Registration_Form_TypeService()
    {
        return $this->services['fos_user.registration.form.type'] = new \FOS\UserBundle\Form\Type\RegistrationFormType('Oleg\\UserdirectoryBundle\\Entity\\User');
    }

    /**
     * Gets the public 'fos_user.resetting.form.factory' shared service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory
     */
    protected function getFosUser_Resetting_Form_FactoryService()
    {
        return $this->services['fos_user.resetting.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory(${($_ = isset($this->services['form.factory']) ? $this->services['form.factory'] : $this->get('form.factory')) && false ?: '_'}, 'fos_user_resetting_form', 'FOS\\UserBundle\\Form\\Type\\ResettingFormType', array(0 => 'ResetPassword', 1 => 'Default'));
    }

    /**
     * Gets the public 'fos_user.resetting.form.type' shared service.
     *
     * @return \FOS\UserBundle\Form\Type\ResettingFormType
     */
    protected function getFosUser_Resetting_Form_TypeService()
    {
        return $this->services['fos_user.resetting.form.type'] = new \FOS\UserBundle\Form\Type\ResettingFormType('Oleg\\UserdirectoryBundle\\Entity\\User');
    }

    /**
     * Gets the public 'fos_user.security.interactive_login_listener' shared service.
     *
     * @return \FOS\UserBundle\EventListener\LastLoginListener
     */
    protected function getFosUser_Security_InteractiveLoginListenerService()
    {
        return $this->services['fos_user.security.interactive_login_listener'] = new \FOS\UserBundle\EventListener\LastLoginListener(${($_ = isset($this->services['fos_user.user_manager']) ? $this->services['fos_user.user_manager'] : $this->get('fos_user.user_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'fos_user.security.login_manager' shared service.
     *
     * @return \FOS\UserBundle\Security\LoginManager
     */
    protected function getFosUser_Security_LoginManagerService()
    {
        return $this->services['fos_user.security.login_manager'] = new \FOS\UserBundle\Security\LoginManager(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, NULL);
    }

    /**
     * Gets the public 'fos_user.user_manager' shared service.
     *
     * @return \FOS\UserBundle\Doctrine\UserManager
     */
    protected function getFosUser_UserManagerService()
    {
        return $this->services['fos_user.user_manager'] = new \FOS\UserBundle\Doctrine\UserManager(${($_ = isset($this->services['fos_user.util.password_updater']) ? $this->services['fos_user.util.password_updater'] : $this->getFosUser_Util_PasswordUpdaterService()) && false ?: '_'}, ${($_ = isset($this->services['fos_user.util.canonical_fields_updater']) ? $this->services['fos_user.util.canonical_fields_updater'] : $this->getFosUser_Util_CanonicalFieldsUpdaterService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'}->getManager(NULL), 'Oleg\\UserdirectoryBundle\\Entity\\User');
    }

    /**
     * Gets the public 'fos_user.username_form_type' shared service.
     *
     * @return \FOS\UserBundle\Form\Type\UsernameFormType
     */
    protected function getFosUser_UsernameFormTypeService()
    {
        return $this->services['fos_user.username_form_type'] = new \FOS\UserBundle\Form\Type\UsernameFormType(new \FOS\UserBundle\Form\DataTransformer\UserToUsernameTransformer(${($_ = isset($this->services['fos_user.user_manager']) ? $this->services['fos_user.user_manager'] : $this->get('fos_user.user_manager')) && false ?: '_'}));
    }

    /**
     * Gets the public 'fos_user.util.email_canonicalizer' shared service.
     *
     * @return \FOS\UserBundle\Util\Canonicalizer
     */
    protected function getFosUser_Util_EmailCanonicalizerService()
    {
        return $this->services['fos_user.util.email_canonicalizer'] = new \FOS\UserBundle\Util\Canonicalizer();
    }

    /**
     * Gets the public 'fos_user.util.token_generator' shared service.
     *
     * @return \FOS\UserBundle\Util\TokenGenerator
     */
    protected function getFosUser_Util_TokenGeneratorService()
    {
        return $this->services['fos_user.util.token_generator'] = new \FOS\UserBundle\Util\TokenGenerator();
    }

    /**
     * Gets the public 'fos_user.util.user_manipulator' shared service.
     *
     * @return \FOS\UserBundle\Util\UserManipulator
     */
    protected function getFosUser_Util_UserManipulatorService()
    {
        return $this->services['fos_user.util.user_manipulator'] = new \FOS\UserBundle\Util\UserManipulator(${($_ = isset($this->services['fos_user.user_manager']) ? $this->services['fos_user.user_manager'] : $this->get('fos_user.user_manager')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher')) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'});
    }

    /**
     * Gets the public 'fragment.handler' shared service.
     *
     * @return \Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler
     */
    protected function getFragment_HandlerService()
    {
        return $this->services['fragment.handler'] = new \Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler(${($_ = isset($this->services['service_locator.e64d23c3bf770e2cf44b71643280668d']) ? $this->services['service_locator.e64d23c3bf770e2cf44b71643280668d'] : $this->getServiceLocator_E64d23c3bf770e2cf44b71643280668dService()) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, true);
    }

    /**
     * Gets the public 'fragment.listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\FragmentListener
     */
    protected function getFragment_ListenerService()
    {
        return $this->services['fragment.listener'] = new \Symfony\Component\HttpKernel\EventListener\FragmentListener(${($_ = isset($this->services['uri_signer']) ? $this->services['uri_signer'] : $this->get('uri_signer')) && false ?: '_'}, '/_fragment');
    }

    /**
     * Gets the public 'fragment.renderer.esi' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer
     */
    protected function getFragment_Renderer_EsiService()
    {
        $this->services['fragment.renderer.esi'] = $instance = new \Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer(NULL, ${($_ = isset($this->services['fragment.renderer.inline']) ? $this->services['fragment.renderer.inline'] : $this->get('fragment.renderer.inline')) && false ?: '_'}, ${($_ = isset($this->services['uri_signer']) ? $this->services['uri_signer'] : $this->get('uri_signer')) && false ?: '_'});

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the public 'fragment.renderer.hinclude' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\HIncludeFragmentRenderer
     */
    protected function getFragment_Renderer_HincludeService()
    {
        $this->services['fragment.renderer.hinclude'] = $instance = new \Symfony\Component\HttpKernel\Fragment\HIncludeFragmentRenderer(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, ${($_ = isset($this->services['uri_signer']) ? $this->services['uri_signer'] : $this->get('uri_signer')) && false ?: '_'}, NULL);

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the public 'fragment.renderer.inline' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer
     */
    protected function getFragment_Renderer_InlineService()
    {
        $this->services['fragment.renderer.inline'] = $instance = new \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher')) && false ?: '_'});

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the public 'fragment.renderer.ssi' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\SsiFragmentRenderer
     */
    protected function getFragment_Renderer_SsiService()
    {
        $this->services['fragment.renderer.ssi'] = $instance = new \Symfony\Component\HttpKernel\Fragment\SsiFragmentRenderer(NULL, ${($_ = isset($this->services['fragment.renderer.inline']) ? $this->services['fragment.renderer.inline'] : $this->get('fragment.renderer.inline')) && false ?: '_'}, ${($_ = isset($this->services['uri_signer']) ? $this->services['uri_signer'] : $this->get('uri_signer')) && false ?: '_'});

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the public 'html2pdf_factory' shared service.
     *
     * @return \Ensepar\Html2pdfBundle\Factory\Html2pdfFactory
     */
    protected function getHtml2pdfFactoryService()
    {
        return $this->services['html2pdf_factory'] = new \Ensepar\Html2pdfBundle\Factory\Html2pdfFactory('P', 'A4', 'en', true, 'UTF-8', array(0 => 10, 1 => 15, 2 => 10, 3 => 15));
    }

    /**
     * Gets the public 'http_kernel' shared service.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    protected function getHttpKernelService()
    {
        return $this->services['http_kernel'] = new \Symfony\Component\HttpKernel\HttpKernel(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher')) && false ?: '_'}, ${($_ = isset($this->services['debug.controller_resolver']) ? $this->services['debug.controller_resolver'] : $this->get('debug.controller_resolver')) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, ${($_ = isset($this->services['debug.argument_resolver']) ? $this->services['debug.argument_resolver'] : $this->get('debug.argument_resolver')) && false ?: '_'});
    }

    /**
     * Gets the public 'kernel.class_cache.cache_warmer' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\CacheWarmer\ClassCacheCacheWarmer
     */
    protected function getKernel_ClassCache_CacheWarmerService()
    {
        return $this->services['kernel.class_cache.cache_warmer'] = new \Symfony\Bundle\FrameworkBundle\CacheWarmer\ClassCacheCacheWarmer(array(0 => 'Symfony\\Component\\HttpFoundation\\ParameterBag', 1 => 'Symfony\\Component\\HttpFoundation\\HeaderBag', 2 => 'Symfony\\Component\\HttpFoundation\\FileBag', 3 => 'Symfony\\Component\\HttpFoundation\\ServerBag', 4 => 'Symfony\\Component\\HttpFoundation\\Request', 5 => 'Symfony\\Component\\HttpKernel\\Kernel'));
    }

    /**
     * Gets the public 'knp_paginator' shared service.
     *
     * @return \Knp\Component\Pager\Paginator
     */
    protected function getKnpPaginatorService()
    {
        $this->services['knp_paginator'] = $instance = new \Knp\Component\Pager\Paginator(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher')) && false ?: '_'});

        $instance->setDefaultPaginatorOptions(array('pageParameterName' => 'page', 'sortFieldParameterName' => 'sort', 'sortDirectionParameterName' => 'direction', 'filterFieldParameterName' => 'filterField', 'filterValueParameterName' => 'filterValue', 'distinct' => true));

        return $instance;
    }

    /**
     * Gets the public 'knp_paginator.helper.processor' shared service.
     *
     * @return \Knp\Bundle\PaginatorBundle\Helper\Processor
     */
    protected function getKnpPaginator_Helper_ProcessorService()
    {
        return $this->services['knp_paginator.helper.processor'] = new \Knp\Bundle\PaginatorBundle\Helper\Processor(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, ${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->get('translator')) && false ?: '_'});
    }

    /**
     * Gets the public 'knp_paginator.subscriber.filtration' shared service.
     *
     * @return \Knp\Component\Pager\Event\Subscriber\Filtration\FiltrationSubscriber
     */
    protected function getKnpPaginator_Subscriber_FiltrationService()
    {
        return $this->services['knp_paginator.subscriber.filtration'] = new \Knp\Component\Pager\Event\Subscriber\Filtration\FiltrationSubscriber();
    }

    /**
     * Gets the public 'knp_paginator.subscriber.paginate' shared service.
     *
     * @return \Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber
     */
    protected function getKnpPaginator_Subscriber_PaginateService()
    {
        return $this->services['knp_paginator.subscriber.paginate'] = new \Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber();
    }

    /**
     * Gets the public 'knp_paginator.subscriber.sliding_pagination' shared service.
     *
     * @return \Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber
     */
    protected function getKnpPaginator_Subscriber_SlidingPaginationService()
    {
        return $this->services['knp_paginator.subscriber.sliding_pagination'] = new \Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber(array('defaultPaginationTemplate' => 'KnpPaginatorBundle:Pagination:sliding.html.twig', 'defaultSortableTemplate' => 'KnpPaginatorBundle:Pagination:sortable_link.html.twig', 'defaultFiltrationTemplate' => 'KnpPaginatorBundle:Pagination:filtration.html.twig', 'defaultPageRange' => 5));
    }

    /**
     * Gets the public 'knp_paginator.subscriber.sortable' shared service.
     *
     * @return \Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber
     */
    protected function getKnpPaginator_Subscriber_SortableService()
    {
        return $this->services['knp_paginator.subscriber.sortable'] = new \Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber();
    }

    /**
     * Gets the public 'knp_paginator.twig.extension.pagination' shared service.
     *
     * @return \Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension
     */
    protected function getKnpPaginator_Twig_Extension_PaginationService()
    {
        return $this->services['knp_paginator.twig.extension.pagination'] = new \Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension(${($_ = isset($this->services['knp_paginator.helper.processor']) ? $this->services['knp_paginator.helper.processor'] : $this->get('knp_paginator.helper.processor')) && false ?: '_'});
    }

    /**
     * Gets the public 'knp_snappy.image' shared service.
     *
     * @return \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator
     */
    protected function getKnpSnappy_ImageService()
    {
        return $this->services['knp_snappy.image'] = new \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator(new \Knp\Snappy\Image('wkhtmltoimage', array(), array()), ${($_ = isset($this->services['monolog.logger.snappy']) ? $this->services['monolog.logger.snappy'] : $this->get('monolog.logger.snappy', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'knp_snappy.pdf' shared service.
     *
     * @return \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator
     */
    protected function getKnpSnappy_PdfService()
    {
        return $this->services['knp_snappy.pdf'] = new \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator(new \Knp\Snappy\Pdf('"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"', array('javascript-delay' => 7000), array()), ${($_ = isset($this->services['monolog.logger.snappy']) ? $this->services['monolog.logger.snappy'] : $this->get('monolog.logger.snappy', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'locale_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\LocaleListener
     */
    protected function getLocaleListenerService()
    {
        return $this->services['locale_listener'] = new \Symfony\Component\HttpKernel\EventListener\LocaleListener(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, 'en', ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'logger' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getLoggerService()
    {
        $this->services['logger'] = $instance = new \Symfony\Bridge\Monolog\Logger('app');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->useMicrosecondTimestamps(true);
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.handler.console' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Handler\ConsoleHandler
     */
    protected function getMonolog_Handler_ConsoleService()
    {
        $this->services['monolog.handler.console'] = $instance = new \Symfony\Bridge\Monolog\Handler\ConsoleHandler(NULL, true, array());

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : $this->getMonolog_Processor_PsrLogMessageService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.handler.file' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonolog_Handler_FileService()
    {
        $this->services['monolog.handler.file'] = $instance = new \Monolog\Handler\StreamHandler(($this->targetDirs[2].'\\logs/dev.log'), 100, true, NULL);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : $this->getMonolog_Processor_PsrLogMessageService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.handler.main' shared service.
     *
     * @return \Monolog\Handler\FingersCrossedHandler
     */
    protected function getMonolog_Handler_MainService()
    {
        $this->services['monolog.handler.main'] = $instance = new \Monolog\Handler\FingersCrossedHandler(${($_ = isset($this->services['monolog.handler.file']) ? $this->services['monolog.handler.file'] : $this->get('monolog.handler.file')) && false ?: '_'}, 250, 0, true, true, NULL);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : $this->getMonolog_Processor_PsrLogMessageService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.handler.null_internal' shared service.
     *
     * @return \Monolog\Handler\NullHandler
     */
    protected function getMonolog_Handler_NullInternalService()
    {
        return $this->services['monolog.handler.null_internal'] = new \Monolog\Handler\NullHandler();
    }

    /**
     * Gets the public 'monolog.handler.syslog' shared service.
     *
     * @return \Monolog\Handler\SyslogHandler
     */
    protected function getMonolog_Handler_SyslogService()
    {
        $this->services['monolog.handler.syslog'] = $instance = new \Monolog\Handler\SyslogHandler(false, 'user', 400, true, 1);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : $this->getMonolog_Processor_PsrLogMessageService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.assetic' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_AsseticService()
    {
        $this->services['monolog.logger.assetic'] = $instance = new \Symfony\Bridge\Monolog\Logger('assetic');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.cache' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_CacheService()
    {
        $this->services['monolog.logger.cache'] = $instance = new \Symfony\Bridge\Monolog\Logger('cache');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.console' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_ConsoleService()
    {
        $this->services['monolog.logger.console'] = $instance = new \Symfony\Bridge\Monolog\Logger('console');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.doctrine' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_DoctrineService()
    {
        $this->services['monolog.logger.doctrine'] = $instance = new \Symfony\Bridge\Monolog\Logger('doctrine');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.event' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_EventService()
    {
        $this->services['monolog.logger.event'] = $instance = new \Symfony\Bridge\Monolog\Logger('event');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.php' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_PhpService()
    {
        $this->services['monolog.logger.php'] = $instance = new \Symfony\Bridge\Monolog\Logger('php');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.profiler' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_ProfilerService()
    {
        $this->services['monolog.logger.profiler'] = $instance = new \Symfony\Bridge\Monolog\Logger('profiler');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.request' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_RequestService()
    {
        $this->services['monolog.logger.request'] = $instance = new \Symfony\Bridge\Monolog\Logger('request');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.router' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_RouterService()
    {
        $this->services['monolog.logger.router'] = $instance = new \Symfony\Bridge\Monolog\Logger('router');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.security' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_SecurityService()
    {
        $this->services['monolog.logger.security'] = $instance = new \Symfony\Bridge\Monolog\Logger('security');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.snappy' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_SnappyService()
    {
        $this->services['monolog.logger.snappy'] = $instance = new \Symfony\Bridge\Monolog\Logger('snappy');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'monolog.logger.templating' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_TemplatingService()
    {
        $this->services['monolog.logger.templating'] = $instance = new \Symfony\Bridge\Monolog\Logger('templating');

        $instance->pushProcessor(${($_ = isset($this->services['debug.log_processor']) ? $this->services['debug.log_processor'] : $this->getDebug_LogProcessorService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->get('monolog.handler.console')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.syslog']) ? $this->services['monolog.handler.syslog'] : $this->get('monolog.handler.syslog')) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.main']) ? $this->services['monolog.handler.main'] : $this->get('monolog.handler.main')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'oleg.handler.session_idle' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Services\SessionIdleHandler
     */
    protected function getOleg_Handler_SessionIdleService()
    {
        return $this->services['oleg.handler.session_idle'] = new \Oleg\UserdirectoryBundle\Services\SessionIdleHandler($this, ${($_ = isset($this->services['session']) ? $this->services['session'] : $this->get('session')) && false ?: '_'}, ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'oleg.listener.maintenance' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Services\MaintenanceListener
     */
    protected function getOleg_Listener_MaintenanceService()
    {
        return $this->services['oleg.listener.maintenance'] = new \Oleg\UserdirectoryBundle\Services\MaintenanceListener($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'oleg.twig.extension.date' shared service.
     *
     * @return \Twig_Extensions_Extension_Date
     */
    protected function getOleg_Twig_Extension_DateService()
    {
        return $this->services['oleg.twig.extension.date'] = new \Twig_Extensions_Extension_Date();
    }

    /**
     * Gets the public 'oleg.type.employees_custom_selector' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType
     */
    protected function getOleg_Type_EmployeesCustomSelectorService()
    {
        return $this->services['oleg.type.employees_custom_selector'] = new \Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'oleg.upload_listener' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Services\UploadListener
     */
    protected function getOleg_UploadListenerService()
    {
        return $this->services['oleg.upload_listener'] = new \Oleg\UserdirectoryBundle\Services\UploadListener($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'oneup_uploader.chunk_manager' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Chunk\ChunkManager
     */
    protected function getOneupUploader_ChunkManagerService()
    {
        return $this->services['oneup_uploader.chunk_manager'] = new \Oneup\UploaderBundle\Uploader\Chunk\ChunkManager(array('maxage' => 604800, 'storage' => array('type' => 'filesystem', 'filesystem' => NULL, 'directory' => (__DIR__.'/uploader/chunks'), 'stream_wrapper' => NULL, 'sync_buffer_size' => '100K', 'prefix' => 'chunks'), 'load_distribution' => true), ${($_ = isset($this->services['oneup_uploader.chunks_storage']) ? $this->services['oneup_uploader.chunks_storage'] : $this->get('oneup_uploader.chunks_storage')) && false ?: '_'});
    }

    /**
     * Gets the public 'oneup_uploader.chunks_storage' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Chunk\Storage\FilesystemStorage
     */
    protected function getOneupUploader_ChunksStorageService()
    {
        return $this->services['oneup_uploader.chunks_storage'] = new \Oneup\UploaderBundle\Uploader\Chunk\Storage\FilesystemStorage((__DIR__.'/uploader/chunks'));
    }

    /**
     * Gets the public 'oneup_uploader.controller.employees_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Controller\DropzoneController
     */
    protected function getOneupUploader_Controller_EmployeesGalleryService()
    {
        return $this->services['oneup_uploader.controller.employees_gallery'] = new \Oneup\UploaderBundle\Controller\DropzoneController($this, ${($_ = isset($this->services['oneup_uploader.storage.employees_gallery']) ? $this->services['oneup_uploader.storage.employees_gallery'] : $this->get('oneup_uploader.storage.employees_gallery')) && false ?: '_'}, ${($_ = isset($this->services['oneup_uploader.error_handler.dropzone']) ? $this->services['oneup_uploader.error_handler.dropzone'] : $this->getOneupUploader_ErrorHandler_DropzoneService()) && false ?: '_'}, array('frontend' => 'dropzone', 'storage' => array('directory' => 'Uploaded/directory/documents', 'service' => NULL, 'type' => 'filesystem', 'filesystem' => NULL, 'stream_wrapper' => NULL, 'sync_buffer_size' => '100K'), 'custom_frontend' => array('name' => NULL, 'class' => NULL), 'route_prefix' => '', 'allowed_mimetypes' => array(), 'disallowed_mimetypes' => array(), 'error_handler' => NULL, 'max_size' => 2147483647, 'use_orphanage' => false, 'enable_progress' => false, 'enable_cancelation' => false, 'namer' => 'oneup_uploader.namer.uniqid', 'root_folder' => false), 'employees_gallery');
    }

    /**
     * Gets the public 'oneup_uploader.controller.fellapp_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Controller\DropzoneController
     */
    protected function getOneupUploader_Controller_FellappGalleryService()
    {
        return $this->services['oneup_uploader.controller.fellapp_gallery'] = new \Oneup\UploaderBundle\Controller\DropzoneController($this, ${($_ = isset($this->services['oneup_uploader.storage.fellapp_gallery']) ? $this->services['oneup_uploader.storage.fellapp_gallery'] : $this->get('oneup_uploader.storage.fellapp_gallery')) && false ?: '_'}, ${($_ = isset($this->services['oneup_uploader.error_handler.dropzone']) ? $this->services['oneup_uploader.error_handler.dropzone'] : $this->getOneupUploader_ErrorHandler_DropzoneService()) && false ?: '_'}, array('frontend' => 'dropzone', 'storage' => array('directory' => 'Uploaded/fellapp/documents', 'service' => NULL, 'type' => 'filesystem', 'filesystem' => NULL, 'stream_wrapper' => NULL, 'sync_buffer_size' => '100K'), 'custom_frontend' => array('name' => NULL, 'class' => NULL), 'route_prefix' => '', 'allowed_mimetypes' => array(), 'disallowed_mimetypes' => array(), 'error_handler' => NULL, 'max_size' => 2147483647, 'use_orphanage' => false, 'enable_progress' => false, 'enable_cancelation' => false, 'namer' => 'oneup_uploader.namer.uniqid', 'root_folder' => false), 'fellapp_gallery');
    }

    /**
     * Gets the public 'oneup_uploader.controller.scan_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Controller\DropzoneController
     */
    protected function getOneupUploader_Controller_ScanGalleryService()
    {
        return $this->services['oneup_uploader.controller.scan_gallery'] = new \Oneup\UploaderBundle\Controller\DropzoneController($this, ${($_ = isset($this->services['oneup_uploader.storage.scan_gallery']) ? $this->services['oneup_uploader.storage.scan_gallery'] : $this->get('oneup_uploader.storage.scan_gallery')) && false ?: '_'}, ${($_ = isset($this->services['oneup_uploader.error_handler.dropzone']) ? $this->services['oneup_uploader.error_handler.dropzone'] : $this->getOneupUploader_ErrorHandler_DropzoneService()) && false ?: '_'}, array('frontend' => 'dropzone', 'storage' => array('directory' => 'Uploaded/scan-order/documents', 'service' => NULL, 'type' => 'filesystem', 'filesystem' => NULL, 'stream_wrapper' => NULL, 'sync_buffer_size' => '100K'), 'custom_frontend' => array('name' => NULL, 'class' => NULL), 'route_prefix' => '', 'allowed_mimetypes' => array(), 'disallowed_mimetypes' => array(), 'error_handler' => NULL, 'max_size' => 2147483647, 'use_orphanage' => false, 'enable_progress' => false, 'enable_cancelation' => false, 'namer' => 'oneup_uploader.namer.uniqid', 'root_folder' => false), 'scan_gallery');
    }

    /**
     * Gets the public 'oneup_uploader.controller.vacreq_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Controller\DropzoneController
     */
    protected function getOneupUploader_Controller_VacreqGalleryService()
    {
        return $this->services['oneup_uploader.controller.vacreq_gallery'] = new \Oneup\UploaderBundle\Controller\DropzoneController($this, ${($_ = isset($this->services['oneup_uploader.storage.vacreq_gallery']) ? $this->services['oneup_uploader.storage.vacreq_gallery'] : $this->get('oneup_uploader.storage.vacreq_gallery')) && false ?: '_'}, ${($_ = isset($this->services['oneup_uploader.error_handler.dropzone']) ? $this->services['oneup_uploader.error_handler.dropzone'] : $this->getOneupUploader_ErrorHandler_DropzoneService()) && false ?: '_'}, array('frontend' => 'dropzone', 'storage' => array('directory' => 'Uploaded/directory/vacreq', 'service' => NULL, 'type' => 'filesystem', 'filesystem' => NULL, 'stream_wrapper' => NULL, 'sync_buffer_size' => '100K'), 'custom_frontend' => array('name' => NULL, 'class' => NULL), 'route_prefix' => '', 'allowed_mimetypes' => array(), 'disallowed_mimetypes' => array(), 'error_handler' => NULL, 'max_size' => 2147483647, 'use_orphanage' => false, 'enable_progress' => false, 'enable_cancelation' => false, 'namer' => 'oneup_uploader.namer.uniqid', 'root_folder' => false), 'vacreq_gallery');
    }

    /**
     * Gets the public 'oneup_uploader.namer.uniqid' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Naming\UniqidNamer
     */
    protected function getOneupUploader_Namer_UniqidService()
    {
        return $this->services['oneup_uploader.namer.uniqid'] = new \Oneup\UploaderBundle\Uploader\Naming\UniqidNamer();
    }

    /**
     * Gets the public 'oneup_uploader.orphanage_manager' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Orphanage\OrphanageManager
     */
    protected function getOneupUploader_OrphanageManagerService()
    {
        return $this->services['oneup_uploader.orphanage_manager'] = new \Oneup\UploaderBundle\Uploader\Orphanage\OrphanageManager($this, array('maxage' => 604800, 'directory' => (__DIR__.'/uploader/orphanage')));
    }

    /**
     * Gets the public 'oneup_uploader.routing.loader' shared service.
     *
     * @return \Oneup\UploaderBundle\Routing\RouteLoader
     */
    protected function getOneupUploader_Routing_LoaderService()
    {
        return $this->services['oneup_uploader.routing.loader'] = new \Oneup\UploaderBundle\Routing\RouteLoader(array('employees_gallery' => array(0 => 'oneup_uploader.controller.employees_gallery', 1 => array('enable_progress' => false, 'enable_cancelation' => false, 'route_prefix' => '')), 'scan_gallery' => array(0 => 'oneup_uploader.controller.scan_gallery', 1 => array('enable_progress' => false, 'enable_cancelation' => false, 'route_prefix' => '')), 'fellapp_gallery' => array(0 => 'oneup_uploader.controller.fellapp_gallery', 1 => array('enable_progress' => false, 'enable_cancelation' => false, 'route_prefix' => '')), 'vacreq_gallery' => array(0 => 'oneup_uploader.controller.vacreq_gallery', 1 => array('enable_progress' => false, 'enable_cancelation' => false, 'route_prefix' => ''))));
    }

    /**
     * Gets the public 'oneup_uploader.storage.employees_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage
     */
    protected function getOneupUploader_Storage_EmployeesGalleryService()
    {
        return $this->services['oneup_uploader.storage.employees_gallery'] = new \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage('Uploaded/directory/documents');
    }

    /**
     * Gets the public 'oneup_uploader.storage.fellapp_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage
     */
    protected function getOneupUploader_Storage_FellappGalleryService()
    {
        return $this->services['oneup_uploader.storage.fellapp_gallery'] = new \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage('Uploaded/fellapp/documents');
    }

    /**
     * Gets the public 'oneup_uploader.storage.scan_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage
     */
    protected function getOneupUploader_Storage_ScanGalleryService()
    {
        return $this->services['oneup_uploader.storage.scan_gallery'] = new \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage('Uploaded/scan-order/documents');
    }

    /**
     * Gets the public 'oneup_uploader.storage.vacreq_gallery' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage
     */
    protected function getOneupUploader_Storage_VacreqGalleryService()
    {
        return $this->services['oneup_uploader.storage.vacreq_gallery'] = new \Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage('Uploaded/directory/vacreq');
    }

    /**
     * Gets the public 'oneup_uploader.templating.uploader_helper' shared service.
     *
     * @return \Oneup\UploaderBundle\Templating\Helper\UploaderHelper
     */
    protected function getOneupUploader_Templating_UploaderHelperService()
    {
        return $this->services['oneup_uploader.templating.uploader_helper'] = new \Oneup\UploaderBundle\Templating\Helper\UploaderHelper(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, array('employees_gallery' => 2097152, 'scan_gallery' => 2097152, 'fellapp_gallery' => 2097152, 'vacreq_gallery' => 2097152));
    }

    /**
     * Gets the public 'oneup_uploader.twig.extension.uploader' shared service.
     *
     * @return \Oneup\UploaderBundle\Twig\Extension\UploaderExtension
     */
    protected function getOneupUploader_Twig_Extension_UploaderService()
    {
        return $this->services['oneup_uploader.twig.extension.uploader'] = new \Oneup\UploaderBundle\Twig\Extension\UploaderExtension(${($_ = isset($this->services['oneup_uploader.templating.uploader_helper']) ? $this->services['oneup_uploader.templating.uploader_helper'] : $this->get('oneup_uploader.templating.uploader_helper')) && false ?: '_'});
    }

    /**
     * Gets the public 'oneup_uploader.validation_listener.allowed_mimetype' shared service.
     *
     * @return \Oneup\UploaderBundle\EventListener\AllowedMimetypeValidationListener
     */
    protected function getOneupUploader_ValidationListener_AllowedMimetypeService()
    {
        return $this->services['oneup_uploader.validation_listener.allowed_mimetype'] = new \Oneup\UploaderBundle\EventListener\AllowedMimetypeValidationListener();
    }

    /**
     * Gets the public 'oneup_uploader.validation_listener.disallowed_mimetype' shared service.
     *
     * @return \Oneup\UploaderBundle\EventListener\DisallowedMimetypeValidationListener
     */
    protected function getOneupUploader_ValidationListener_DisallowedMimetypeService()
    {
        return $this->services['oneup_uploader.validation_listener.disallowed_mimetype'] = new \Oneup\UploaderBundle\EventListener\DisallowedMimetypeValidationListener();
    }

    /**
     * Gets the public 'oneup_uploader.validation_listener.max_size' shared service.
     *
     * @return \Oneup\UploaderBundle\EventListener\MaxSizeValidationListener
     */
    protected function getOneupUploader_ValidationListener_MaxSizeService()
    {
        return $this->services['oneup_uploader.validation_listener.max_size'] = new \Oneup\UploaderBundle\EventListener\MaxSizeValidationListener();
    }

    /**
     * Gets the public 'order_form.type.scan_custom_selector' shared service.
     *
     * @return \Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType
     */
    protected function getOrderForm_Type_ScanCustomSelectorService()
    {
        return $this->services['order_form.type.scan_custom_selector'] = new \Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'order_security_utility' shared service.
     *
     * @return \Oleg\OrderformBundle\Security\Util\SecurityUtil
     */
    protected function getOrderSecurityUtilityService()
    {
        return $this->services['order_security_utility'] = new \Oleg\OrderformBundle\Security\Util\SecurityUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->get('security.authorization_checker')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'profiler' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Profiler\Profiler
     */
    protected function getProfilerService()
    {
        $a = ${($_ = isset($this->services['monolog.logger.profiler']) ? $this->services['monolog.logger.profiler'] : $this->get('monolog.logger.profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $b = ${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $c = new \Symfony\Component\Cache\DataCollector\CacheDataCollector();
        $c->addInstance('cache.app', ${($_ = isset($this->services['cache.app']) ? $this->services['cache.app'] : $this->get('cache.app')) && false ?: '_'});
        $c->addInstance('cache.system', ${($_ = isset($this->services['cache.system']) ? $this->services['cache.system'] : $this->get('cache.system')) && false ?: '_'});
        $c->addInstance('cache.validator', ${($_ = isset($this->services['cache.validator']) ? $this->services['cache.validator'] : $this->getCache_ValidatorService()) && false ?: '_'});
        $c->addInstance('cache.serializer', new \Symfony\Component\Cache\Adapter\TraceableAdapter(${($_ = isset($this->services['cache.serializer.recorder_inner']) ? $this->services['cache.serializer.recorder_inner'] : $this->getCache_Serializer_RecorderInnerService()) && false ?: '_'}));
        $c->addInstance('cache.annotations', ${($_ = isset($this->services['cache.annotations']) ? $this->services['cache.annotations'] : $this->getCache_AnnotationsService()) && false ?: '_'});

        $d = new \Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine')) && false ?: '_'});
        $d->addLogger('default', ${($_ = isset($this->services['doctrine.dbal.logger.profiling.default']) ? $this->services['doctrine.dbal.logger.profiling.default'] : $this->getDoctrine_Dbal_Logger_Profiling_DefaultService()) && false ?: '_'});
        $d->addLogger('aperio', ${($_ = isset($this->services['doctrine.dbal.logger.profiling.aperio']) ? $this->services['doctrine.dbal.logger.profiling.aperio'] : $this->getDoctrine_Dbal_Logger_Profiling_AperioService()) && false ?: '_'});

        $e = new \Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector();
        if ($this->has('kernel')) {
            $e->setKernel($b);
        }

        $this->services['profiler'] = $instance = new \Symfony\Component\HttpKernel\Profiler\Profiler(new \Symfony\Component\HttpKernel\Profiler\FileProfilerStorage(('file:'.__DIR__.'/profiler')), $a);

        $instance->add(${($_ = isset($this->services['data_collector.request']) ? $this->services['data_collector.request'] : $this->get('data_collector.request')) && false ?: '_'});
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\TimeDataCollector($b, ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}));
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\MemoryDataCollector());
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\AjaxDataCollector());
        $instance->add(${($_ = isset($this->services['data_collector.form']) ? $this->services['data_collector.form'] : $this->get('data_collector.form')) && false ?: '_'});
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector());
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\LoggerDataCollector($a, (__DIR__.'/appDevDebugProjectContainer')));
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\EventDataCollector(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}));
        $instance->add(${($_ = isset($this->services['data_collector.router']) ? $this->services['data_collector.router'] : $this->get('data_collector.router')) && false ?: '_'});
        $instance->add($c);
        $instance->add(new \Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['security.role_hierarchy']) ? $this->services['security.role_hierarchy'] : $this->getSecurity_RoleHierarchyService()) && false ?: '_'}, ${($_ = isset($this->services['security.logout_url_generator']) ? $this->services['security.logout_url_generator'] : $this->getSecurity_LogoutUrlGeneratorService()) && false ?: '_'}, ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['security.firewall.map']) ? $this->services['security.firewall.map'] : $this->getSecurity_Firewall_MapService()) && false ?: '_'}));
        $instance->add(new \Symfony\Bridge\Twig\DataCollector\TwigDataCollector(${($_ = isset($this->services['twig.profile']) ? $this->services['twig.profile'] : $this->get('twig.profile')) && false ?: '_'}));
        $instance->add($d);
        $instance->add(new \Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector($this));
        $instance->add(${($_ = isset($this->services['data_collector.dump']) ? $this->services['data_collector.dump'] : $this->get('data_collector.dump')) && false ?: '_'});
        $instance->add($e);

        return $instance;
    }

    /**
     * Gets the public 'profiler_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ProfilerListener
     */
    protected function getProfilerListenerService()
    {
        return $this->services['profiler_listener'] = new \Symfony\Component\HttpKernel\EventListener\ProfilerListener(${($_ = isset($this->services['profiler']) ? $this->services['profiler'] : $this->get('profiler')) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, NULL, false, false);
    }

    /**
     * Gets the public 'property_accessor' shared service.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected function getPropertyAccessorService()
    {
        return $this->services['property_accessor'] = new \Symfony\Component\PropertyAccess\PropertyAccessor(false, false, new \Symfony\Component\Cache\Adapter\ArrayAdapter(0, false));
    }

    /**
     * Gets the public 'request_stack' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    protected function getRequestStackService()
    {
        return $this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack();
    }

    /**
     * Gets the public 'response_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ResponseListener
     */
    protected function getResponseListenerService()
    {
        return $this->services['response_listener'] = new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8');
    }

    /**
     * Gets the public 'router' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouterService()
    {
        $this->services['router'] = $instance = new \Symfony\Bundle\FrameworkBundle\Routing\Router($this, (__DIR__.'/assetic/routing.yml'), array('cache_dir' => __DIR__, 'debug' => true, 'generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper', 'generator_cache_class' => 'appDevDebugProjectContainerUrlGenerator', 'matcher_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher', 'matcher_base_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher', 'matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper', 'matcher_cache_class' => 'appDevDebugProjectContainerUrlMatcher', 'strict_requirements' => true, 'resource_type' => 'yaml'), ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.router']) ? $this->services['monolog.logger.router'] : $this->get('monolog.logger.router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});

        $instance->setConfigCacheFactory(${($_ = isset($this->services['config_cache_factory']) ? $this->services['config_cache_factory'] : $this->get('config_cache_factory')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'router_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\RouterListener
     */
    protected function getRouterListenerService()
    {
        return $this->services['router_listener'] = new \Symfony\Component\HttpKernel\EventListener\RouterListener(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'}, ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.request']) ? $this->services['monolog.logger.request'] : $this->get('monolog.logger.request', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'routing.loader' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader
     */
    protected function getRouting_LoaderService()
    {
        $a = ${($_ = isset($this->services['file_locator']) ? $this->services['file_locator'] : $this->get('file_locator')) && false ?: '_'};
        $b = ${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->get('annotation_reader')) && false ?: '_'};

        $c = new \Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader($b);

        $d = new \Symfony\Component\Config\Loader\LoaderResolver();
        $d->addLoader(new \Symfony\Component\Routing\Loader\XmlFileLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\YamlFileLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\PhpFileLoader($a));
        $d->addLoader(new \Symfony\Component\Config\Loader\GlobFileLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\DirectoryLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\DependencyInjection\ServiceRouterLoader($this));
        $d->addLoader(new \Symfony\Bundle\AsseticBundle\Routing\AsseticLoader(${($_ = isset($this->services['assetic.asset_manager']) ? $this->services['assetic.asset_manager'] : $this->get('assetic.asset_manager')) && false ?: '_'}, array()));
        $d->addLoader(new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader($a, $c));
        $d->addLoader(new \Symfony\Component\Routing\Loader\AnnotationFileLoader($a, $c));
        $d->addLoader($c);
        $d->addLoader(${($_ = isset($this->services['oneup_uploader.routing.loader']) ? $this->services['oneup_uploader.routing.loader'] : $this->get('oneup_uploader.routing.loader')) && false ?: '_'});

        return $this->services['routing.loader'] = new \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader(${($_ = isset($this->services['controller_name_converter']) ? $this->services['controller_name_converter'] : $this->getControllerNameConverterService()) && false ?: '_'}, $d);
    }

    /**
     * Gets the public 'scanorder_utility' shared service.
     *
     * @return \Oleg\OrderformBundle\Helper\OrderUtil
     */
    protected function getScanorderUtilityService()
    {
        return $this->services['scanorder_utility'] = new \Oleg\OrderformBundle\Helper\OrderUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'search_utility' shared service.
     *
     * @return \Oleg\OrderformBundle\Helper\SearchUtil
     */
    protected function getSearchUtilityService()
    {
        return $this->services['search_utility'] = new \Oleg\OrderformBundle\Helper\SearchUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'security.authentication.guard_handler' shared service.
     *
     * @return \Symfony\Component\Security\Guard\GuardAuthenticatorHandler
     */
    protected function getSecurity_Authentication_GuardHandlerService()
    {
        return $this->services['security.authentication.guard_handler'] = new \Symfony\Component\Security\Guard\GuardAuthenticatorHandler(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'security.authentication_utils' shared service.
     *
     * @return \Symfony\Component\Security\Http\Authentication\AuthenticationUtils
     */
    protected function getSecurity_AuthenticationUtilsService()
    {
        return $this->services['security.authentication_utils'] = new \Symfony\Component\Security\Http\Authentication\AuthenticationUtils(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'});
    }

    /**
     * Gets the public 'security.authorization_checker' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    protected function getSecurity_AuthorizationCheckerService()
    {
        return $this->services['security.authorization_checker'] = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'}, ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, false);
    }

    /**
     * Gets the public 'security.csrf.token_manager' shared service.
     *
     * @return \Symfony\Component\Security\Csrf\CsrfTokenManager
     */
    protected function getSecurity_Csrf_TokenManagerService()
    {
        return $this->services['security.csrf.token_manager'] = new \Symfony\Component\Security\Csrf\CsrfTokenManager(new \Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator(), new \Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage(${($_ = isset($this->services['session']) ? $this->services['session'] : $this->get('session')) && false ?: '_'}));
    }

    /**
     * Gets the public 'security.encoder_factory' shared service.
     *
     * @return \Symfony\Component\Security\Core\Encoder\EncoderFactory
     */
    protected function getSecurity_EncoderFactoryService()
    {
        return $this->services['security.encoder_factory'] = new \Symfony\Component\Security\Core\Encoder\EncoderFactory(array('FOS\\UserBundle\\Model\\UserInterface' => array('algorithm' => 'sha512', 'hash_algorithm' => 'sha512', 'key_length' => 40, 'ignore_case' => false, 'encode_as_base64' => true, 'iterations' => 5000, 'cost' => 13)));
    }

    /**
     * Gets the public 'security.firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\EventListener\FirewallListener
     */
    protected function getSecurity_FirewallService()
    {
        return $this->services['security.firewall'] = new \Symfony\Bundle\SecurityBundle\EventListener\FirewallListener(${($_ = isset($this->services['security.firewall.map']) ? $this->services['security.firewall.map'] : $this->getSecurity_Firewall_MapService()) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher')) && false ?: '_'}, ${($_ = isset($this->services['security.logout_url_generator']) ? $this->services['security.logout_url_generator'] : $this->getSecurity_LogoutUrlGeneratorService()) && false ?: '_'});
    }

    /**
     * Gets the public 'security.firewall.map.context.aperio_ldap_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_AperioLdapFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['authentication_handler']) ? $this->services['authentication_handler'] : $this->get('authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'aperio_ldap_firewall', array('lifetime' => 604800, 'path' => '/scan', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/scan/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/scan/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/scan/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'aperio_ldap_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/scan/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'aperio_ldap_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/scan/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.aperio_ldap_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'aperio_ldap_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'aperio_ldap_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/scan/login', false), '/scan/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('aperio_ldap_firewall', 'security.user_checker', 'security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.aperio_ldap_firewall', NULL, '/scan/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.firewall.map.context.ldap_calllog_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_LdapCalllogFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['calllog_authentication_handler']) ? $this->services['calllog_authentication_handler'] : $this->get('calllog_authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_calllog_firewall', array('lifetime' => 604800, 'path' => '/call-log-book', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/call-log-book/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/call-log-book/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/call-log-book/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'ldap_calllog_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/call-log-book/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'ldap_calllog_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/call-log-book/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.ldap_calllog_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'ldap_calllog_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'ldap_calllog_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/call-log-book/login', false), '/call-log-book/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('ldap_calllog_firewall', 'security.user_checker', 'security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.ldap_calllog_firewall', NULL, '/call-log-book/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.firewall.map.context.ldap_deidentifier_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_LdapDeidentifierFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['deidentifier_authentication_handler']) ? $this->services['deidentifier_authentication_handler'] : $this->get('deidentifier_authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_deidentifier_firewall', array('lifetime' => 604800, 'path' => '/deidentifier', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/deidentifier/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/deidentifier/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/deidentifier/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'ldap_deidentifier_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/deidentifier/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'ldap_deidentifier_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/deidentifier/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.ldap_deidentifier_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'ldap_deidentifier_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'ldap_deidentifier_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/deidentifier/login', false), '/deidentifier/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('ldap_deidentifier_firewall', 'security.user_checker', 'security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.ldap_deidentifier_firewall', NULL, '/deidentifier/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.firewall.map.context.ldap_employees_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_LdapEmployeesFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['employees_authentication_handler']) ? $this->services['employees_authentication_handler'] : $this->get('employees_authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_employees_firewall', array('lifetime' => 604800, 'path' => '/directory', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/directory/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/directory/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/directory/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'ldap_employees_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/directory/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'ldap_employees_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/directory/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.ldap_employees_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'ldap_employees_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'ldap_employees_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/directory/login', false), '/directory/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('ldap_employees_firewall', 'security.user_checker', 'security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.ldap_employees_firewall', NULL, '/directory/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.firewall.map.context.ldap_fellapp_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_LdapFellappFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['fellapp_authentication_handler']) ? $this->services['fellapp_authentication_handler'] : $this->get('fellapp_authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_fellapp_firewall', array('lifetime' => 604800, 'path' => '/fellowship-applications', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/fellowship-applications/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/fellowship-applications/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/fellowship-applications/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'ldap_fellapp_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/fellowship-applications/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'ldap_fellapp_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/fellowship-applications/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.ldap_fellapp_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'ldap_fellapp_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'ldap_fellapp_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/fellowship-applications/login', false), '/fellowship-applications/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('ldap_fellapp_firewall', 'security.user_checker', 'security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.ldap_fellapp_firewall', NULL, '/fellowship-applications/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.firewall.map.context.ldap_translationalresearch_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_LdapTranslationalresearchFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['translationalresearch_authentication_handler']) ? $this->services['translationalresearch_authentication_handler'] : $this->get('translationalresearch_authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_translationalresearch_firewall', array('lifetime' => 604800, 'path' => '/translational-research', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/translational-research/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/translational-research/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/translational-research/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'ldap_translationalresearch_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/translational-research/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'ldap_translationalresearch_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/translational-research/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.ldap_translationalresearch_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'ldap_translationalresearch_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'ldap_translationalresearch_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/translational-research/login', false), '/translational-research/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('ldap_translationalresearch_firewall', 'security.user_checker', 'security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.ldap_translationalresearch_firewall', NULL, '/translational-research/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.firewall.map.context.ldap_vacreq_firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext
     */
    protected function getSecurity_Firewall_Map_Context_LdapVacreqFirewallService()
    {
        $a = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->getSecurity_HttpUtilsService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $d = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'};
        $e = ${($_ = isset($this->services['vacreq_authentication_handler']) ? $this->services['vacreq_authentication_handler'] : $this->get('vacreq_authentication_handler')) && false ?: '_'};
        $f = ${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'};
        $g = ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.authentication.session_strategy']) ? $this->services['security.authentication.session_strategy'] : $this->getSecurity_Authentication_SessionStrategyService()) && false ?: '_'};
        $i = ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        $j = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $b), '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_vacreq_firewall', array('lifetime' => 604800, 'path' => '/vacation-request', 'name' => 'REMEMBERME', 'domain' => NULL, 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $c);

        $k = new \Symfony\Component\Security\Http\Firewall\LogoutListener($d, $a, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($a, '/vacation-request/login'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/vacation-request/logout'));
        $k->addHandler(${($_ = isset($this->services['security.logout.handler.session']) ? $this->services['security.logout.handler.session'] : $this->getSecurity_Logout_Handler_SessionService()) && false ?: '_'});
        $k->addHandler($j);

        $l = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationSuccessHandler($e, array('use_referer' => true, 'login_path' => '/vacation-request/login', 'always_use_default_target_path' => false, 'default_target_path' => '/', 'target_path_parameter' => '_target_path'), 'ldap_vacreq_firewall');

        $m = new \Symfony\Component\Security\Http\Authentication\CustomAuthenticationFailureHandler($e, array('login_path' => '/vacation-request/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $n = new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler($f, $l, $m, $c);

        $o = new \Symfony\Component\Security\Http\Firewall\SimpleFormAuthenticationListener($d, $g, $h, $a, 'ldap_vacreq_firewall', $n, $n, array('authenticator' => 'custom_authenticator', 'check_path' => '/vacation-request/login_check', 'use_forward' => false, 'require_previous_session' => true, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $c, $i, NULL, $f);
        $o->setRememberMeServices($j);

        return $this->services['security.firewall.map.context.ldap_vacreq_firewall'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->getSecurity_ChannelListenerService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['security.context_listener.0']) ? $this->services['security.context_listener.0'] : $this->getSecurity_ContextListener_0Service()) && false ?: '_'}, 2 => $k, 3 => $o, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($d, $j, $g, $c, $i, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($d, '599db05a161ef7.25162865', $c, $g), 6 => new \Symfony\Component\Security\Http\Firewall\SwitchUserListener($d, $b, ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, 'ldap_vacreq_firewall', ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, $c, '_switch_user', 'ROLE_ALLOWED_TO_SWITCH', $i), 7 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->getSecurity_AccessListenerService()) && false ?: '_'}), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($d, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, $a, 'ldap_vacreq_firewall', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, $a, '/vacation-request/login', false), '/vacation-request/access-requests/new/create', NULL, $c, false), new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('ldap_vacreq_firewall', 'security.user_checker', 'security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e', true, false, 'fos_user.user_provider.username', 'scan_auth', 'security.authentication.form_entry_point.ldap_vacreq_firewall', NULL, '/vacation-request/access-requests/new/create', array(0 => 'logout', 1 => 'switch_user', 2 => 'simple_form', 3 => 'remember_me', 4 => 'anonymous')));
    }

    /**
     * Gets the public 'security.password_encoder' shared service.
     *
     * @return \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder
     */
    protected function getSecurity_PasswordEncoderService()
    {
        return $this->services['security.password_encoder'] = new \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder(${($_ = isset($this->services['security.encoder_factory']) ? $this->services['security.encoder_factory'] : $this->get('security.encoder_factory')) && false ?: '_'});
    }

    /**
     * Gets the public 'security.rememberme.response_listener' shared service.
     *
     * @return \Symfony\Component\Security\Http\RememberMe\ResponseListener
     */
    protected function getSecurity_Rememberme_ResponseListenerService()
    {
        return $this->services['security.rememberme.response_listener'] = new \Symfony\Component\Security\Http\RememberMe\ResponseListener();
    }

    /**
     * Gets the public 'security.token_storage' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    protected function getSecurity_TokenStorageService()
    {
        return $this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
    }

    /**
     * Gets the public 'security.validator.user_password' shared service.
     *
     * @return \Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator
     */
    protected function getSecurity_Validator_UserPasswordService()
    {
        return $this->services['security.validator.user_password'] = new \Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['security.encoder_factory']) ? $this->services['security.encoder_factory'] : $this->get('security.encoder_factory')) && false ?: '_'});
    }

    /**
     * Gets the public 'sensio_distribution.security_checker' shared service.
     *
     * @return \SensioLabs\Security\SecurityChecker
     */
    protected function getSensioDistribution_SecurityCheckerService()
    {
        return $this->services['sensio_distribution.security_checker'] = new \SensioLabs\Security\SecurityChecker();
    }

    /**
     * Gets the public 'sensio_distribution.security_checker.command' shared service.
     *
     * @return \SensioLabs\Security\Command\SecurityCheckerCommand
     */
    protected function getSensioDistribution_SecurityChecker_CommandService()
    {
        return $this->services['sensio_distribution.security_checker.command'] = new \SensioLabs\Security\Command\SecurityCheckerCommand(${($_ = isset($this->services['sensio_distribution.security_checker']) ? $this->services['sensio_distribution.security_checker'] : $this->get('sensio_distribution.security_checker')) && false ?: '_'});
    }

    /**
     * Gets the public 'sensio_framework_extra.cache.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener
     */
    protected function getSensioFrameworkExtra_Cache_ListenerService()
    {
        return $this->services['sensio_framework_extra.cache.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener();
    }

    /**
     * Gets the public 'sensio_framework_extra.controller.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener
     */
    protected function getSensioFrameworkExtra_Controller_ListenerService()
    {
        return $this->services['sensio_framework_extra.controller.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->get('annotation_reader')) && false ?: '_'});
    }

    /**
     * Gets the public 'sensio_framework_extra.converter.datetime' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter
     */
    protected function getSensioFrameworkExtra_Converter_DatetimeService()
    {
        return $this->services['sensio_framework_extra.converter.datetime'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter();
    }

    /**
     * Gets the public 'sensio_framework_extra.converter.doctrine.orm' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter
     */
    protected function getSensioFrameworkExtra_Converter_Doctrine_OrmService()
    {
        return $this->services['sensio_framework_extra.converter.doctrine.orm'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->get('doctrine', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'sensio_framework_extra.converter.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener
     */
    protected function getSensioFrameworkExtra_Converter_ListenerService()
    {
        return $this->services['sensio_framework_extra.converter.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener(${($_ = isset($this->services['sensio_framework_extra.converter.manager']) ? $this->services['sensio_framework_extra.converter.manager'] : $this->get('sensio_framework_extra.converter.manager')) && false ?: '_'}, true);
    }

    /**
     * Gets the public 'sensio_framework_extra.converter.manager' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager
     */
    protected function getSensioFrameworkExtra_Converter_ManagerService()
    {
        $this->services['sensio_framework_extra.converter.manager'] = $instance = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager();

        $instance->add(${($_ = isset($this->services['sensio_framework_extra.converter.doctrine.orm']) ? $this->services['sensio_framework_extra.converter.doctrine.orm'] : $this->get('sensio_framework_extra.converter.doctrine.orm')) && false ?: '_'}, 0, 'doctrine.orm');
        $instance->add(${($_ = isset($this->services['sensio_framework_extra.converter.datetime']) ? $this->services['sensio_framework_extra.converter.datetime'] : $this->get('sensio_framework_extra.converter.datetime')) && false ?: '_'}, 0, 'datetime');

        return $instance;
    }

    /**
     * Gets the public 'sensio_framework_extra.security.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener
     */
    protected function getSensioFrameworkExtra_Security_ListenerService()
    {
        return $this->services['sensio_framework_extra.security.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener(NULL, new \Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage(), ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, ${($_ = isset($this->services['security.role_hierarchy']) ? $this->services['security.role_hierarchy'] : $this->getSecurity_RoleHierarchyService()) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'sensio_framework_extra.view.guesser' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser
     */
    protected function getSensioFrameworkExtra_View_GuesserService()
    {
        return $this->services['sensio_framework_extra.view.guesser'] = new \Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'});
    }

    /**
     * Gets the public 'sensio_framework_extra.view.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener
     */
    protected function getSensioFrameworkExtra_View_ListenerService()
    {
        return $this->services['sensio_framework_extra.view.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener($this);
    }

    /**
     * Gets the public 'session' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSessionService()
    {
        return $this->services['session'] = new \Symfony\Component\HttpFoundation\Session\Session(${($_ = isset($this->services['session.storage.native']) ? $this->services['session.storage.native'] : $this->get('session.storage.native')) && false ?: '_'}, new \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag(), new \Symfony\Component\HttpFoundation\Session\Flash\FlashBag());
    }

    /**
     * Gets the public 'session.handler' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler
     */
    protected function getSession_HandlerService()
    {
        return $this->services['session.handler'] = new \Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler((__DIR__.'/sessions'));
    }

    /**
     * Gets the public 'session.save_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\SaveSessionListener
     */
    protected function getSession_SaveListenerService()
    {
        return $this->services['session.save_listener'] = new \Symfony\Component\HttpKernel\EventListener\SaveSessionListener();
    }

    /**
     * Gets the public 'session.storage.filesystem' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage
     */
    protected function getSession_Storage_FilesystemService()
    {
        return $this->services['session.storage.filesystem'] = new \Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage((__DIR__.'/sessions'), 'MOCKSESSID', ${($_ = isset($this->services['session.storage.metadata_bag']) ? $this->services['session.storage.metadata_bag'] : $this->getSession_Storage_MetadataBagService()) && false ?: '_'});
    }

    /**
     * Gets the public 'session.storage.native' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage
     */
    protected function getSession_Storage_NativeService()
    {
        return $this->services['session.storage.native'] = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(array('cookie_httponly' => true, 'gc_probability' => 1), ${($_ = isset($this->services['session.handler']) ? $this->services['session.handler'] : $this->get('session.handler')) && false ?: '_'}, ${($_ = isset($this->services['session.storage.metadata_bag']) ? $this->services['session.storage.metadata_bag'] : $this->getSession_Storage_MetadataBagService()) && false ?: '_'});
    }

    /**
     * Gets the public 'session.storage.php_bridge' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage
     */
    protected function getSession_Storage_PhpBridgeService()
    {
        return $this->services['session.storage.php_bridge'] = new \Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage(${($_ = isset($this->services['session.handler']) ? $this->services['session.handler'] : $this->get('session.handler')) && false ?: '_'}, ${($_ = isset($this->services['session.storage.metadata_bag']) ? $this->services['session.storage.metadata_bag'] : $this->getSession_Storage_MetadataBagService()) && false ?: '_'});
    }

    /**
     * Gets the public 'session_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\SessionListener
     */
    protected function getSessionListenerService()
    {
        return $this->services['session_listener'] = new \Symfony\Component\HttpKernel\EventListener\SessionListener(new \Symfony\Component\DependencyInjection\ServiceLocator(array('session' => function () {
            return ${($_ = isset($this->services['session']) ? $this->services['session'] : $this->get('session', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        })));
    }

    /**
     * Gets the public 'spraed.pdf.generator' shared service.
     *
     * @return \Spraed\PDFGeneratorBundle\PDFGenerator\PDFGenerator
     */
    protected function getSpraed_Pdf_GeneratorService()
    {
        return $this->services['spraed.pdf.generator'] = new \Spraed\PDFGeneratorBundle\PDFGenerator\PDFGenerator(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'});
    }

    /**
     * Gets the public 'stof_doctrine_extensions.uploadable.manager' shared service.
     *
     * @return \Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager
     */
    protected function getStofDoctrineExtensions_Uploadable_ManagerService()
    {
        $a = new \Gedmo\Uploadable\UploadableListener(new \Stof\DoctrineExtensionsBundle\Uploadable\MimeTypeGuesserAdapter());
        $a->setAnnotationReader(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->get('annotation_reader')) && false ?: '_'});
        $a->setDefaultFileInfoClass('Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo');

        return $this->services['stof_doctrine_extensions.uploadable.manager'] = new \Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager($a, 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo');
    }

    /**
     * Gets the public 'streamed_response_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener
     */
    protected function getStreamedResponseListenerService()
    {
        return $this->services['streamed_response_listener'] = new \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener();
    }

    /**
     * Gets the public 'swiftmailer.email_sender.listener' shared service.
     *
     * @return \Symfony\Bundle\SwiftmailerBundle\EventListener\EmailSenderListener
     */
    protected function getSwiftmailer_EmailSender_ListenerService()
    {
        return $this->services['swiftmailer.email_sender.listener'] = new \Symfony\Bundle\SwiftmailerBundle\EventListener\EmailSenderListener($this, ${($_ = isset($this->services['logger']) ? $this->services['logger'] : $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'swiftmailer.mailer.default' shared service.
     *
     * @return \Swift_Mailer
     */
    protected function getSwiftmailer_Mailer_DefaultService()
    {
        return $this->services['swiftmailer.mailer.default'] = new \Swift_Mailer(${($_ = isset($this->services['swiftmailer.mailer.default.transport']) ? $this->services['swiftmailer.mailer.default.transport'] : $this->get('swiftmailer.mailer.default.transport')) && false ?: '_'});
    }

    /**
     * Gets the public 'swiftmailer.mailer.default.plugin.messagelogger' shared service.
     *
     * @return \Swift_Plugins_MessageLogger
     */
    protected function getSwiftmailer_Mailer_Default_Plugin_MessageloggerService()
    {
        return $this->services['swiftmailer.mailer.default.plugin.messagelogger'] = new \Swift_Plugins_MessageLogger();
    }

    /**
     * Gets the public 'swiftmailer.mailer.default.spool' shared service.
     *
     * @return \Swift_FileSpool
     */
    protected function getSwiftmailer_Mailer_Default_SpoolService()
    {
        return $this->services['swiftmailer.mailer.default.spool'] = new \Swift_FileSpool(($this->targetDirs[3].'\\app/spool/default'));
    }

    /**
     * Gets the public 'swiftmailer.mailer.default.transport' shared service.
     *
     * @return \Swift_Transport_SpoolTransport
     */
    protected function getSwiftmailer_Mailer_Default_TransportService()
    {
        $this->services['swiftmailer.mailer.default.transport'] = $instance = new \Swift_Transport_SpoolTransport(${($_ = isset($this->services['swiftmailer.mailer.default.transport.eventdispatcher']) ? $this->services['swiftmailer.mailer.default.transport.eventdispatcher'] : $this->getSwiftmailer_Mailer_Default_Transport_EventdispatcherService()) && false ?: '_'}, ${($_ = isset($this->services['swiftmailer.mailer.default.spool']) ? $this->services['swiftmailer.mailer.default.spool'] : $this->get('swiftmailer.mailer.default.spool')) && false ?: '_'});

        $instance->registerPlugin(${($_ = isset($this->services['swiftmailer.mailer.default.plugin.messagelogger']) ? $this->services['swiftmailer.mailer.default.plugin.messagelogger'] : $this->get('swiftmailer.mailer.default.plugin.messagelogger')) && false ?: '_'});
        $instance->registerPlugin(${($_ = isset($this->services['swiftmailer.plugin.redirecting']) ? $this->services['swiftmailer.plugin.redirecting'] : $this->get('swiftmailer.plugin.redirecting')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'swiftmailer.mailer.default.transport.real' shared service.
     *
     * @return \Swift_Transport_EsmtpTransport
     */
    protected function getSwiftmailer_Mailer_Default_Transport_RealService()
    {
        $a = new \Swift_Transport_Esmtp_AuthHandler(array(0 => new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator(), 1 => new \Swift_Transport_Esmtp_Auth_LoginAuthenticator(), 2 => new \Swift_Transport_Esmtp_Auth_PlainAuthenticator()));
        $a->setUsername(NULL);
        $a->setPassword(NULL);
        $a->setAuthMode(NULL);

        $this->services['swiftmailer.mailer.default.transport.real'] = $instance = new \Swift_Transport_EsmtpTransport(new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()), array(0 => $a), ${($_ = isset($this->services['swiftmailer.mailer.default.transport.eventdispatcher']) ? $this->services['swiftmailer.mailer.default.transport.eventdispatcher'] : $this->getSwiftmailer_Mailer_Default_Transport_EventdispatcherService()) && false ?: '_'});

        $instance->setHost('smtp.med.cornell.edu');
        $instance->setPort(25);
        $instance->setEncryption(NULL);
        $instance->setTimeout(30);
        $instance->setSourceIp(NULL);
        (new \Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SmtpTransportConfigurator(NULL, ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'}))->configure($instance);

        return $instance;
    }

    /**
     * Gets the public 'swiftmailer.plugin.redirecting' shared service.
     *
     * @return \Swift_Plugins_RedirectingPlugin
     */
    protected function getSwiftmailer_Plugin_RedirectingService()
    {
        return $this->services['swiftmailer.plugin.redirecting'] = new \Swift_Plugins_RedirectingPlugin(array(0 => 'oli2002@med.cornell.edu'), array());
    }

    /**
     * Gets the public 'templating' shared service.
     *
     * @return \Symfony\Bundle\TwigBundle\TwigEngine
     */
    protected function getTemplatingService()
    {
        return $this->services['templating'] = new \Symfony\Bundle\TwigBundle\TwigEngine(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, ${($_ = isset($this->services['templating.name_parser']) ? $this->services['templating.name_parser'] : $this->get('templating.name_parser')) && false ?: '_'}, ${($_ = isset($this->services['templating.locator']) ? $this->services['templating.locator'] : $this->getTemplating_LocatorService()) && false ?: '_'});
    }

    /**
     * Gets the public 'templating.filename_parser' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\TemplateFilenameParser
     */
    protected function getTemplating_FilenameParserService()
    {
        return $this->services['templating.filename_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateFilenameParser();
    }

    /**
     * Gets the public 'templating.helper.logout_url' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Templating\Helper\LogoutUrlHelper
     */
    protected function getTemplating_Helper_LogoutUrlService()
    {
        return $this->services['templating.helper.logout_url'] = new \Symfony\Bundle\SecurityBundle\Templating\Helper\LogoutUrlHelper(${($_ = isset($this->services['security.logout_url_generator']) ? $this->services['security.logout_url_generator'] : $this->getSecurity_LogoutUrlGeneratorService()) && false ?: '_'});
    }

    /**
     * Gets the public 'templating.helper.security' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Templating\Helper\SecurityHelper
     */
    protected function getTemplating_Helper_SecurityService()
    {
        return $this->services['templating.helper.security'] = new \Symfony\Bundle\SecurityBundle\Templating\Helper\SecurityHelper(${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'templating.loader' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader
     */
    protected function getTemplating_LoaderService()
    {
        return $this->services['templating.loader'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader(${($_ = isset($this->services['templating.locator']) ? $this->services['templating.locator'] : $this->getTemplating_LocatorService()) && false ?: '_'});
    }

    /**
     * Gets the public 'templating.name_parser' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser
     */
    protected function getTemplating_NameParserService()
    {
        return $this->services['templating.name_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'});
    }

    /**
     * Gets the public 'translationalresearch_authentication_handler' shared service.
     *
     * @return \Oleg\TranslationalResearchBundle\Security\Authentication\TranslationalResearchLoginSuccessHandler
     */
    protected function getTranslationalresearchAuthenticationHandlerService()
    {
        return $this->services['translationalresearch_authentication_handler'] = new \Oleg\TranslationalResearchBundle\Security\Authentication\TranslationalResearchLoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'translator' shared service.
     *
     * @return \Symfony\Component\Translation\IdentityTranslator
     */
    protected function getTranslatorService()
    {
        return $this->services['translator'] = new \Symfony\Component\Translation\IdentityTranslator(new \Symfony\Component\Translation\MessageSelector());
    }

    /**
     * Gets the public 'twig' shared service.
     *
     * @return \Twig\Environment
     */
    protected function getTwigService()
    {
        $a = ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};
        $b = ${($_ = isset($this->services['debug.file_link_formatter']) ? $this->services['debug.file_link_formatter'] : $this->getDebug_FileLinkFormatterService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'};

        $d = new \Symfony\Component\VarDumper\Dumper\HtmlDumper(NULL, 'UTF-8', 0);
        $d->setDisplayOptions(array('fileLinkFormat' => $b));

        $e = new \Symfony\Component\VarDumper\Dumper\HtmlDumper(NULL, 'UTF-8', 1);
        $e->setDisplayOptions(array('maxStringLength' => 4096, 'fileLinkFormat' => $b));

        $f = new \Symfony\Bridge\Twig\AppVariable();
        $f->setEnvironment('dev');
        $f->setDebug(true);
        if ($this->has('security.token_storage')) {
            $f->setTokenStorage(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
        }
        if ($this->has('request_stack')) {
            $f->setRequestStack($c);
        }

        $this->services['twig'] = $instance = new \Twig\Environment(${($_ = isset($this->services['twig.loader']) ? $this->services['twig.loader'] : $this->get('twig.loader')) && false ?: '_'}, array('form_themes' => array(0 => 'form_div_layout.html.twig'), 'debug' => true, 'strict_variables' => true, 'exception_controller' => 'twig.controller.exception:showAction', 'autoescape' => 'name', 'cache' => (__DIR__.'/twig'), 'charset' => 'UTF-8', 'paths' => array(), 'date' => array('format' => 'F j, Y H:i', 'interval_format' => '%d days', 'timezone' => NULL), 'number_format' => array('decimals' => 0, 'decimal_point' => '.', 'thousands_separator' => ',')));

        $instance->addExtension(${($_ = isset($this->services['oleg.twig.extension.date']) ? $this->services['oleg.twig.extension.date'] : $this->get('oleg.twig.extension.date')) && false ?: '_'});
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\LogoutUrlExtension(${($_ = isset($this->services['security.logout_url_generator']) ? $this->services['security.logout_url_generator'] : $this->getSecurity_LogoutUrlGeneratorService()) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\SecurityExtension(${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\ProfilerExtension(${($_ = isset($this->services['twig.profile']) ? $this->services['twig.profile'] : $this->get('twig.profile')) && false ?: '_'}, $a));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension(${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->get('translator')) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\AssetExtension(${($_ = isset($this->services['assets.packages']) ? $this->services['assets.packages'] : $this->get('assets.packages')) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\CodeExtension($b, ($this->targetDirs[3].'\\app'), 'UTF-8'));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\RoutingExtension(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router')) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\YamlExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\StopwatchExtension($a, true));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\ExpressionExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\HttpKernelExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\HttpFoundationExtension($c, ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'}));
        $instance->addExtension(new \Twig\Extension\DebugExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension(array(0 => $this, 1 => 'twig.form.renderer')));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\WebLinkExtension($c));
        $instance->addExtension(new \Symfony\Bundle\AsseticBundle\Twig\AsseticExtension(${($_ = isset($this->services['assetic.asset_factory']) ? $this->services['assetic.asset_factory'] : $this->getAssetic_AssetFactoryService()) && false ?: '_'}, ${($_ = isset($this->services['templating.name_parser']) ? $this->services['templating.name_parser'] : $this->get('templating.name_parser')) && false ?: '_'}, true, array(), array(0 => 'OlegUserdirectoryBundle', 1 => 'OlegOrderformBundle', 2 => 'OlegFellAppBundle', 3 => 'OlegDeidentifierBundle', 4 => 'OlegVacReqBundle', 5 => 'OlegCallLogBundle', 6 => 'OlegTranslationalResearchBundle'), new \Symfony\Bundle\AsseticBundle\DefaultValueSupplier($this)));
        $instance->addExtension(new \Doctrine\Bundle\DoctrineBundle\Twig\DoctrineExtension());
        $instance->addExtension(${($_ = isset($this->services['knp_paginator.twig.extension.pagination']) ? $this->services['knp_paginator.twig.extension.pagination'] : $this->get('knp_paginator.twig.extension.pagination')) && false ?: '_'});
        $instance->addExtension(${($_ = isset($this->services['oneup_uploader.twig.extension.uploader']) ? $this->services['oneup_uploader.twig.extension.uploader'] : $this->get('oneup_uploader.twig.extension.uploader')) && false ?: '_'});
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\DumpExtension(${($_ = isset($this->services['var_dumper.cloner']) ? $this->services['var_dumper.cloner'] : $this->get('var_dumper.cloner')) && false ?: '_'}, $d));
        $instance->addExtension(new \Symfony\Bundle\WebProfilerBundle\Twig\WebProfilerExtension($e));
        $instance->addGlobal('app', $f);
        $instance->addRuntimeLoader(new \Twig\RuntimeLoader\ContainerRuntimeLoader(new \Symfony\Component\DependencyInjection\ServiceLocator(array('Symfony\\Bridge\\Twig\\Extension\\HttpKernelRuntime' => function () {
            return ${($_ = isset($this->services['twig.runtime.httpkernel']) ? $this->services['twig.runtime.httpkernel'] : $this->get('twig.runtime.httpkernel')) && false ?: '_'};
        }, 'Symfony\\Bridge\\Twig\\Form\\TwigRenderer' => function () {
            return ${($_ = isset($this->services['twig.form.renderer']) ? $this->services['twig.form.renderer'] : $this->get('twig.form.renderer')) && false ?: '_'};
        }))));
        $instance->addGlobal('order_security_utility', ${($_ = isset($this->services['order_security_utility']) ? $this->services['order_security_utility'] : $this->get('order_security_utility')) && false ?: '_'});
        $instance->addGlobal('scan_sitename', 'scan');
        $instance->addGlobal('fellapp_sitename', 'fellapp');
        $instance->addGlobal('fellapp_util', ${($_ = isset($this->services['fellapp_util']) ? $this->services['fellapp_util'] : $this->get('fellapp_util')) && false ?: '_'});
        $instance->addGlobal('vacreq_sitename', 'vacreq');
        $instance->addGlobal('vacreq_util', ${($_ = isset($this->services['vacreq_util']) ? $this->services['vacreq_util'] : $this->get('vacreq_util')) && false ?: '_'});
        $instance->addGlobal('deidentifier_sitename', 'deidentifier');
        $instance->addGlobal('calllog_sitename', 'calllog');
        $instance->addGlobal('calllog_util', ${($_ = isset($this->services['calllog_util']) ? $this->services['calllog_util'] : $this->get('calllog_util')) && false ?: '_'});
        $instance->addGlobal('translationalresearch_sitename', 'translationalresearch');
        $instance->addGlobal('institution_url', 'http://www.cornell.edu/');
        $instance->addGlobal('institution_name', 'Cornell University');
        $instance->addGlobal('subinstitution_url', 'http://weill.cornell.edu');
        $instance->addGlobal('subinstitution_name', 'Weill Cornell Medicine');
        $instance->addGlobal('department_url', 'http://www.cornellpathology.com');
        $instance->addGlobal('department_name', 'Pathology and Laboratory Medicine Department');
        $instance->addGlobal('employees_sitename', 'employees');
        $instance->addGlobal('default_system_email', 'oli2002@med.cornell.edu');
        $instance->addGlobal('mainhome_title', 'Welcome to the O R D E R platform!');
        $instance->addGlobal('listmanager_title', 'List Manager');
        $instance->addGlobal('eventlog_title', 'Event Log');
        $instance->addGlobal('sitesettings_title', 'Site Settings');
        $instance->addGlobal('contentabout_page', '<p>
                    This site is built on the platform titled "O R D E R" (as in the opposite of disorder).
                </p>

                <p>
                    Designers: Victor Brodsky, Oleg Ivanov
                </p>

                <p>
                    Developer: Oleg Ivanov
                </p>

                <p>
                    Quality Assurance Testers: Oleg Ivanov, Steven Bowe, Emilio Madrigal
                </p>

                <p>
                    We are continuing to improve this software. If you have a suggestion or believe you have encountered an issue, please don\'t hesitate to email
                <a href="mailto:slidescan@med.cornell.edu" target="_top">slidescan@med.cornell.edu</a> and attach relevant screenshots.
                </p>

                <br>

                <p>
                O R D E R is made possible by:
                </p>

                <br>

                <p>

                        <ul>


                    <li>
                        <a href="http://php.net">PHP</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://symfony.com">Symfony</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://doctrine-project.org">Doctrine</a>
                    </li>

                    <br>                  
					
					<li>
                        <a href="https://msdn.microsoft.com/en-us/library/aa366156.aspx">MSDN library: ldap_bind_s</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/SwiftmailerBundle">SwiftmailerBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/AsseticBundle">AsseticBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/FriendsOfSymfony/FOSUserBundle">FOSUserBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://phpexcel.codeplex.com/">PHP Excel</a>
                    </li>

                    <br>

                    <li>

                        <a href="https://github.com/1up-lab/OneupUploaderBundle">OneupUploaderBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.dropzonejs.com/">Dropzone JS</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.jstree.com/">jsTree</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpPaginatorBundle">KnpPaginatorBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://twig.sensiolabs.org/doc/advanced.html">Twig</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://getbootstrap.com/">Bootstrap</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/kriskowal/q">JS promises Q</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jquery.com">jQuery</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jqueryui.com/">jQuery UI</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/RobinHerbots/jquery.inputmask">jQuery Inputmask</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://ivaynberg.github.io/select2/">Select2</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.eyecon.ro/bootstrap-datepicker/">Bootstrap Datepicker</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.malot.fr/bootstrap-datetimepicker/demo.php">Bootstrap DateTime Picker</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/twitter/typeahead.js/">Typeahead with Bloodhound</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://fengyuanchen.github.io/cropper/">Image Cropper</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://handsontable.com/">Handsontable</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpSnappyBundle">KnpSnappyBundle with wkhtmltopdf</a>
                    </li>

                     <br>

                    <li>
                        <a href="https://www.libreoffice.org/">LibreOffice</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/myokyawhtun/PDFMerger">PDFMerger</a>
                    </li>

                    <br>                 

                    <li>
                        <a href="https://github.com/bermi/password-generator">Password Generator</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/andreausu/UsuScryptPasswordEncoderBundle">Password Encoder</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/adesigns/calendar-bundle">jQuery FullCalendar bundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://sciactive.com/pnotify/">PNotify JavaScript notifications</a>
                    </li>

                </ul>
                </p>');
        $instance->addGlobal('employees_uploadpath', 'Uploaded/directory/documents');
        $instance->addGlobal('scan_uploadpath', 'Uploaded/scan-order/documents');
        $instance->addGlobal('fellapp_uploadpath', 'Uploaded/fellapp/documents');
        $instance->addGlobal('vacreq_uploadpath', 'Uploaded/directory/vacreq');
        $instance->addGlobal('user_security_utility', ${($_ = isset($this->services['user_security_utility']) ? $this->services['user_security_utility'] : $this->get('user_security_utility')) && false ?: '_'});
        $instance->addGlobal('user_formnode_utility', ${($_ = isset($this->services['user_formnode_utility']) ? $this->services['user_formnode_utility'] : $this->get('user_formnode_utility')) && false ?: '_'});
        $instance->addGlobal('user_service_utility', ${($_ = isset($this->services['user_service_utility']) ? $this->services['user_service_utility'] : $this->get('user_service_utility')) && false ?: '_'});
        $instance->addGlobal('calllog_util_form', ${($_ = isset($this->services['calllog_util_form']) ? $this->services['calllog_util_form'] : $this->get('calllog_util_form')) && false ?: '_'});
        (new \Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\EnvironmentConfigurator('F j, Y H:i', '%d days', NULL, 0, '.', ','))->configure($instance);

        return $instance;
    }

    /**
     * Gets the public 'twig.controller.exception' shared service.
     *
     * @return \Symfony\Bundle\TwigBundle\Controller\ExceptionController
     */
    protected function getTwig_Controller_ExceptionService()
    {
        return $this->services['twig.controller.exception'] = new \Symfony\Bundle\TwigBundle\Controller\ExceptionController(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, true);
    }

    /**
     * Gets the public 'twig.controller.preview_error' shared service.
     *
     * @return \Symfony\Bundle\TwigBundle\Controller\PreviewErrorController
     */
    protected function getTwig_Controller_PreviewErrorService()
    {
        return $this->services['twig.controller.preview_error'] = new \Symfony\Bundle\TwigBundle\Controller\PreviewErrorController(${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->get('http_kernel')) && false ?: '_'}, 'twig.controller.exception:showAction');
    }

    /**
     * Gets the public 'twig.exception_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ExceptionListener
     */
    protected function getTwig_ExceptionListenerService()
    {
        return $this->services['twig.exception_listener'] = new \Symfony\Component\HttpKernel\EventListener\ExceptionListener('twig.controller.exception:showAction', ${($_ = isset($this->services['monolog.logger.request']) ? $this->services['monolog.logger.request'] : $this->get('monolog.logger.request', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'twig.form.renderer' shared service.
     *
     * @return \Symfony\Bridge\Twig\Form\TwigRenderer
     */
    protected function getTwig_Form_RendererService()
    {
        return $this->services['twig.form.renderer'] = new \Symfony\Bridge\Twig\Form\TwigRenderer(new \Symfony\Bridge\Twig\Form\TwigRendererEngine(array(0 => 'form_div_layout.html.twig'), ${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}), ${($_ = isset($this->services['security.csrf.token_manager']) ? $this->services['security.csrf.token_manager'] : $this->get('security.csrf.token_manager', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'twig.loader' shared service.
     *
     * @return \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader
     */
    protected function getTwig_LoaderService()
    {
        $this->services['twig.loader'] = $instance = new \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader(${($_ = isset($this->services['templating.locator']) ? $this->services['templating.locator'] : $this->getTemplating_LocatorService()) && false ?: '_'}, ${($_ = isset($this->services['templating.name_parser']) ? $this->services['templating.name_parser'] : $this->get('templating.name_parser')) && false ?: '_'}, $this->targetDirs[3]);

        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\FrameworkBundle/Resources/views'), 'Framework');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\SecurityBundle/Resources/views'), 'Security');
        $instance->addPath(($this->targetDirs[3].'\\app/Resources/TwigBundle/views'), 'Twig');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\TwigBundle/Resources/views'), 'Twig');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\swiftmailer-bundle/Resources/views'), 'Swiftmailer');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\doctrine\\doctrine-bundle/Resources/views'), 'Doctrine');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\knplabs\\knp-paginator-bundle/Resources/views'), 'KnpPaginator');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\OrderformBundle/Resources/views'), 'FOSUser');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\friendsofsymfony\\user-bundle/Resources/views'), 'FOSUser');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\adesigns\\calendar-bundle\\ADesigns\\CalendarBundle/Resources/views'), 'ADesignsCalendar');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\UserdirectoryBundle/Resources/views'), 'OlegUserdirectory');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\OrderformBundle/Resources/views'), 'OlegOrderform');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\FellAppBundle/Resources/views'), 'OlegFellApp');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\DeidentifierBundle/Resources/views'), 'OlegDeidentifier');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\VacReqBundle/Resources/views'), 'OlegVacReq');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\CallLogBundle/Resources/views'), 'OlegCallLog');
        $instance->addPath(($this->targetDirs[3].'\\src\\Oleg\\TranslationalResearchBundle/Resources/views'), 'OlegTranslationalResearch');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\DebugBundle/Resources/views'), 'Debug');
        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\WebProfilerBundle/Resources/views'), 'WebProfiler');
        $instance->addPath(($this->targetDirs[3].'\\app/Resources/views'));
        $instance->addPath(($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bridge\\Twig/Resources/views/Form'));

        return $instance;
    }

    /**
     * Gets the public 'twig.profile' shared service.
     *
     * @return \Twig\Profiler\Profile
     */
    protected function getTwig_ProfileService()
    {
        return $this->services['twig.profile'] = new \Twig\Profiler\Profile();
    }

    /**
     * Gets the public 'twig.runtime.httpkernel' shared service.
     *
     * @return \Symfony\Bridge\Twig\Extension\HttpKernelRuntime
     */
    protected function getTwig_Runtime_HttpkernelService()
    {
        return $this->services['twig.runtime.httpkernel'] = new \Symfony\Bridge\Twig\Extension\HttpKernelRuntime(${($_ = isset($this->services['fragment.handler']) ? $this->services['fragment.handler'] : $this->get('fragment.handler')) && false ?: '_'});
    }

    /**
     * Gets the public 'twig.translation.extractor' shared service.
     *
     * @return \Symfony\Bridge\Twig\Translation\TwigExtractor
     */
    protected function getTwig_Translation_ExtractorService()
    {
        return $this->services['twig.translation.extractor'] = new \Symfony\Bridge\Twig\Translation\TwigExtractor(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'});
    }

    /**
     * Gets the public 'twigdate.listener.request' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Services\TwigDateRequestListener
     */
    protected function getTwigdate_Listener_RequestService()
    {
        return $this->services['twigdate.listener.request'] = new \Oleg\UserdirectoryBundle\Services\TwigDateRequestListener(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, 'America/New_York');
    }

    /**
     * Gets the public 'uri_signer' shared service.
     *
     * @return \Symfony\Component\HttpKernel\UriSigner
     */
    protected function getUriSignerService()
    {
        return $this->services['uri_signer'] = new \Symfony\Component\HttpKernel\UriSigner('563fd817cf2c4f1f692d90650b6fba50f782ccc9');
    }

    /**
     * Gets the public 'user_download_utility' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Util\UserDownloadUtil
     */
    protected function getUserDownloadUtilityService()
    {
        return $this->services['user_download_utility'] = new \Oleg\UserdirectoryBundle\Util\UserDownloadUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'user_formnode_utility' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Util\FormNodeUtil
     */
    protected function getUserFormnodeUtilityService()
    {
        return $this->services['user_formnode_utility'] = new \Oleg\UserdirectoryBundle\Util\FormNodeUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'user_generator' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Util\UserGenerator
     */
    protected function getUserGeneratorService()
    {
        return $this->services['user_generator'] = new \Oleg\UserdirectoryBundle\Util\UserGenerator(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'user_mailer_utility' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Util\EmailUtil
     */
    protected function getUserMailerUtilityService()
    {
        return $this->services['user_mailer_utility'] = new \Oleg\UserdirectoryBundle\Util\EmailUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'user_security_utility' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil
     */
    protected function getUserSecurityUtilityService()
    {
        return $this->services['user_security_utility'] = new \Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->get('security.authorization_checker')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'user_service_utility' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Util\UserServiceUtil
     */
    protected function getUserServiceUtilityService()
    {
        return $this->services['user_service_utility'] = new \Oleg\UserdirectoryBundle\Util\UserServiceUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'vacreq_authentication_handler' shared service.
     *
     * @return \Oleg\VacReqBundle\Security\Authentication\VacReqLoginSuccessHandler
     */
    protected function getVacreqAuthenticationHandlerService()
    {
        return $this->services['vacreq_authentication_handler'] = new \Oleg\VacReqBundle\Security\Authentication\VacReqLoginSuccessHandler($this, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'});
    }

    /**
     * Gets the public 'vacreq_awaycalendar_listener' shared service.
     *
     * @return \Oleg\VacReqBundle\EventListener\CalendarEventListener
     */
    protected function getVacreqAwaycalendarListenerService()
    {
        return $this->services['vacreq_awaycalendar_listener'] = new \Oleg\VacReqBundle\EventListener\CalendarEventListener(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'vacreq_import_data' shared service.
     *
     * @return \Oleg\VacReqBundle\Util\VacReqImportData
     */
    protected function getVacreqImportDataService()
    {
        return $this->services['vacreq_import_data'] = new \Oleg\VacReqBundle\Util\VacReqImportData(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'vacreq_util' shared service.
     *
     * @return \Oleg\VacReqBundle\Util\VacReqUtil
     */
    protected function getVacreqUtilService()
    {
        return $this->services['vacreq_util'] = new \Oleg\VacReqBundle\Util\VacReqUtil(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->get('security.authorization_checker')) && false ?: '_'}, $this);
    }

    /**
     * Gets the public 'validate_request_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener
     */
    protected function getValidateRequestListenerService()
    {
        return $this->services['validate_request_listener'] = new \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener();
    }

    /**
     * Gets the public 'validator' shared service.
     *
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function getValidatorService()
    {
        return $this->services['validator'] = ${($_ = isset($this->services['validator.builder']) ? $this->services['validator.builder'] : $this->get('validator.builder')) && false ?: '_'}->getValidator();
    }

    /**
     * Gets the public 'validator.builder' shared service.
     *
     * @return \Symfony\Component\Validator\ValidatorBuilderInterface
     */
    protected function getValidator_BuilderService()
    {
        $this->services['validator.builder'] = $instance = \Symfony\Component\Validator\Validation::createValidatorBuilder();

        $instance->setConstraintValidatorFactory(new \Symfony\Component\Validator\ContainerConstraintValidatorFactory(new \Symfony\Component\DependencyInjection\ServiceLocator(array('Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator' => function () {
            return ${($_ = isset($this->services['doctrine.orm.validator.unique']) ? $this->services['doctrine.orm.validator.unique'] : $this->get('doctrine.orm.validator.unique')) && false ?: '_'};
        }, 'Symfony\\Component\\Security\\Core\\Validator\\Constraints\\UserPasswordValidator' => function () {
            return ${($_ = isset($this->services['security.validator.user_password']) ? $this->services['security.validator.user_password'] : $this->get('security.validator.user_password')) && false ?: '_'};
        }, 'Symfony\\Component\\Validator\\Constraints\\EmailValidator' => function () {
            return ${($_ = isset($this->services['validator.email']) ? $this->services['validator.email'] : $this->get('validator.email')) && false ?: '_'};
        }, 'Symfony\\Component\\Validator\\Constraints\\ExpressionValidator' => function () {
            return ${($_ = isset($this->services['validator.expression']) ? $this->services['validator.expression'] : $this->get('validator.expression')) && false ?: '_'};
        }, 'doctrine.orm.validator.unique' => function () {
            return ${($_ = isset($this->services['doctrine.orm.validator.unique']) ? $this->services['doctrine.orm.validator.unique'] : $this->get('doctrine.orm.validator.unique')) && false ?: '_'};
        }, 'security.validator.user_password' => function () {
            return ${($_ = isset($this->services['security.validator.user_password']) ? $this->services['security.validator.user_password'] : $this->get('security.validator.user_password')) && false ?: '_'};
        }, 'validator.expression' => function () {
            return ${($_ = isset($this->services['validator.expression']) ? $this->services['validator.expression'] : $this->get('validator.expression')) && false ?: '_'};
        }))));
        $instance->setTranslator(${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->get('translator')) && false ?: '_'});
        $instance->setTranslationDomain('validators');
        $instance->addXmlMappings(array(0 => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Component\\Form/Resources/config/validation.xml'), 1 => ($this->targetDirs[3].'\\vendor\\friendsofsymfony\\user-bundle/Resources/config/validation.xml')));
        $instance->enableAnnotationMapping(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->get('annotation_reader')) && false ?: '_'});
        $instance->addMethodMapping('loadValidatorMetadata');
        $instance->addObjectInitializers(array(0 => ${($_ = isset($this->services['doctrine.orm.validator_initializer']) ? $this->services['doctrine.orm.validator_initializer'] : $this->get('doctrine.orm.validator_initializer')) && false ?: '_'}, 1 => new \FOS\UserBundle\Validator\Initializer(${($_ = isset($this->services['fos_user.util.canonical_fields_updater']) ? $this->services['fos_user.util.canonical_fields_updater'] : $this->getFosUser_Util_CanonicalFieldsUpdaterService()) && false ?: '_'})));
        $instance->addXmlMapping(($this->targetDirs[3].'\\vendor\\friendsofsymfony\\user-bundle\\DependencyInjection\\Compiler/../../Resources/config/storage-validation/orm.xml'));

        return $instance;
    }

    /**
     * Gets the public 'validator.email' shared service.
     *
     * @return \Symfony\Component\Validator\Constraints\EmailValidator
     */
    protected function getValidator_EmailService()
    {
        return $this->services['validator.email'] = new \Symfony\Component\Validator\Constraints\EmailValidator(false);
    }

    /**
     * Gets the public 'validator.expression' shared service.
     *
     * @return \Symfony\Component\Validator\Constraints\ExpressionValidator
     */
    protected function getValidator_ExpressionService()
    {
        return $this->services['validator.expression'] = new \Symfony\Component\Validator\Constraints\ExpressionValidator();
    }

    /**
     * Gets the public 'var_dumper.cli_dumper' shared service.
     *
     * @return \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    protected function getVarDumper_CliDumperService()
    {
        return $this->services['var_dumper.cli_dumper'] = new \Symfony\Component\VarDumper\Dumper\CliDumper(NULL, 'UTF-8', 0);
    }

    /**
     * Gets the public 'var_dumper.cloner' shared service.
     *
     * @return \Symfony\Component\VarDumper\Cloner\VarCloner
     */
    protected function getVarDumper_ClonerService()
    {
        $this->services['var_dumper.cloner'] = $instance = new \Symfony\Component\VarDumper\Cloner\VarCloner();

        $instance->setMaxItems(2500);
        $instance->setMaxString(-1);

        return $instance;
    }

    /**
     * Gets the public 'web_profiler.controller.exception' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController
     */
    protected function getWebProfiler_Controller_ExceptionService()
    {
        return $this->services['web_profiler.controller.exception'] = new \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController(${($_ = isset($this->services['profiler']) ? $this->services['profiler'] : $this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, true);
    }

    /**
     * Gets the public 'web_profiler.controller.profiler' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController
     */
    protected function getWebProfiler_Controller_ProfilerService()
    {
        return $this->services['web_profiler.controller.profiler'] = new \Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController(${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['profiler']) ? $this->services['profiler'] : $this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, array('data_collector.request' => array(0 => 'request', 1 => '@WebProfiler/Collector/request.html.twig'), 'data_collector.time' => array(0 => 'time', 1 => '@WebProfiler/Collector/time.html.twig'), 'data_collector.memory' => array(0 => 'memory', 1 => '@WebProfiler/Collector/memory.html.twig'), 'data_collector.ajax' => array(0 => 'ajax', 1 => '@WebProfiler/Collector/ajax.html.twig'), 'data_collector.form' => array(0 => 'form', 1 => '@WebProfiler/Collector/form.html.twig'), 'data_collector.exception' => array(0 => 'exception', 1 => '@WebProfiler/Collector/exception.html.twig'), 'data_collector.logger' => array(0 => 'logger', 1 => '@WebProfiler/Collector/logger.html.twig'), 'data_collector.events' => array(0 => 'events', 1 => '@WebProfiler/Collector/events.html.twig'), 'data_collector.router' => array(0 => 'router', 1 => '@WebProfiler/Collector/router.html.twig'), 'data_collector.cache' => array(0 => 'cache', 1 => '@WebProfiler/Collector/cache.html.twig'), 'data_collector.security' => array(0 => 'security', 1 => '@Security/Collector/security.html.twig'), 'data_collector.twig' => array(0 => 'twig', 1 => '@WebProfiler/Collector/twig.html.twig'), 'data_collector.doctrine' => array(0 => 'db', 1 => '@Doctrine/Collector/db.html.twig'), 'swiftmailer.data_collector' => array(0 => 'swiftmailer', 1 => '@Swiftmailer/Collector/swiftmailer.html.twig'), 'data_collector.dump' => array(0 => 'dump', 1 => '@Debug/Profiler/dump.html.twig'), 'data_collector.config' => array(0 => 'config', 1 => '@WebProfiler/Collector/config.html.twig')), 'bottom', ${($_ = isset($this->services['web_profiler.csp.handler']) ? $this->services['web_profiler.csp.handler'] : $this->getWebProfiler_Csp_HandlerService()) && false ?: '_'}, $this->targetDirs[3]);
    }

    /**
     * Gets the public 'web_profiler.controller.router' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Controller\RouterController
     */
    protected function getWebProfiler_Controller_RouterService()
    {
        return $this->services['web_profiler.controller.router'] = new \Symfony\Bundle\WebProfilerBundle\Controller\RouterController(${($_ = isset($this->services['profiler']) ? $this->services['profiler'] : $this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the public 'web_profiler.debug_toolbar' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener
     */
    protected function getWebProfiler_DebugToolbarService()
    {
        return $this->services['web_profiler.debug_toolbar'] = new \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->get('twig')) && false ?: '_'}, false, 2, 'bottom', ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, '^/(app(_[\\w]+)?\\.php/)?_wdt', ${($_ = isset($this->services['web_profiler.csp.handler']) ? $this->services['web_profiler.csp.handler'] : $this->getWebProfiler_Csp_HandlerService()) && false ?: '_'});
    }

    /**
     * Gets the private '1_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Config\ContainerParametersResourceChecker
     */
    protected function get18fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602Service()
    {
        return $this->services['1_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602'] = new \Symfony\Component\DependencyInjection\Config\ContainerParametersResourceChecker($this);
    }

    /**
     * Gets the private '2_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602' shared service.
     *
     * @return \Symfony\Component\Config\Resource\SelfCheckingResourceChecker
     */
    protected function get28fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602Service()
    {
        return $this->services['2_8fb5e697b78a2df3b8643afda4dabc0330a1f6c92a194d3f2b10f32637437602'] = new \Symfony\Component\Config\Resource\SelfCheckingResourceChecker();
    }

    /**
     * Gets the private 'annotations.reader' shared service.
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    protected function getAnnotations_ReaderService()
    {
        $a = new \Doctrine\Common\Annotations\AnnotationRegistry();
        $a->registerLoader('class_exists');

        $this->services['annotations.reader'] = $instance = new \Doctrine\Common\Annotations\AnnotationReader();

        $instance->addGlobalIgnoredName('required', $a);

        return $instance;
    }

    /**
     * Gets the private 'argument_resolver.default' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver
     */
    protected function getArgumentResolver_DefaultService()
    {
        return $this->services['argument_resolver.default'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver();
    }

    /**
     * Gets the private 'argument_resolver.request' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver
     */
    protected function getArgumentResolver_RequestService()
    {
        return $this->services['argument_resolver.request'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver();
    }

    /**
     * Gets the private 'argument_resolver.request_attribute' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver
     */
    protected function getArgumentResolver_RequestAttributeService()
    {
        return $this->services['argument_resolver.request_attribute'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver();
    }

    /**
     * Gets the private 'argument_resolver.service' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver
     */
    protected function getArgumentResolver_ServiceService()
    {
        return $this->services['argument_resolver.service'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver(new \Symfony\Component\DependencyInjection\ServiceLocator(array()));
    }

    /**
     * Gets the private 'argument_resolver.session' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver
     */
    protected function getArgumentResolver_SessionService()
    {
        return $this->services['argument_resolver.session'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver();
    }

    /**
     * Gets the private 'argument_resolver.variadic' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver
     */
    protected function getArgumentResolver_VariadicService()
    {
        return $this->services['argument_resolver.variadic'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver();
    }

    /**
     * Gets the private 'assetic.asset_factory' shared service.
     *
     * @return \Symfony\Bundle\AsseticBundle\Factory\AssetFactory
     */
    protected function getAssetic_AssetFactoryService()
    {
        $this->services['assetic.asset_factory'] = $instance = new \Symfony\Bundle\AsseticBundle\Factory\AssetFactory(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'}, $this, $this->getParameterBag(), ($this->targetDirs[3].'\\app/../web'), true);

        $instance->addWorker(new \Symfony\Bundle\AsseticBundle\Factory\Worker\UseControllerWorker());

        return $instance;
    }

    /**
     * Gets the private 'cache.annotations' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCache_AnnotationsService()
    {
        return $this->services['cache.annotations'] = new \Symfony\Component\Cache\Adapter\TraceableAdapter(${($_ = isset($this->services['cache.annotations.recorder_inner']) ? $this->services['cache.annotations.recorder_inner'] : $this->getCache_Annotations_RecorderInnerService()) && false ?: '_'});
    }

    /**
     * Gets the private 'cache.annotations.recorder_inner' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\AdapterInterface
     */
    protected function getCache_Annotations_RecorderInnerService($lazyLoad = true)
    {
        return $this->services['cache.annotations.recorder_inner'] = \Symfony\Component\Cache\Adapter\AbstractAdapter::createSystemCache('2QhTBRV-mH', 0, 'iMVxxS3A7m2OslZxnASj9m', (__DIR__.'/pools'), ${($_ = isset($this->services['monolog.logger.cache']) ? $this->services['monolog.logger.cache'] : $this->get('monolog.logger.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'cache.app.recorder_inner' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\FilesystemAdapter
     */
    protected function getCache_App_RecorderInnerService($lazyLoad = true)
    {
        $this->services['cache.app.recorder_inner'] = $instance = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('dZ+N07-Pe3', 0, (__DIR__.'/pools'));

        if ($this->has('monolog.logger.cache')) {
            $instance->setLogger(${($_ = isset($this->services['monolog.logger.cache']) ? $this->services['monolog.logger.cache'] : $this->get('monolog.logger.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
        }

        return $instance;
    }

    /**
     * Gets the private 'cache.serializer.recorder_inner' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\AdapterInterface
     */
    protected function getCache_Serializer_RecorderInnerService($lazyLoad = true)
    {
        return $this->services['cache.serializer.recorder_inner'] = \Symfony\Component\Cache\Adapter\AbstractAdapter::createSystemCache('HgJu3oOurV', 0, 'iMVxxS3A7m2OslZxnASj9m', (__DIR__.'/pools'), ${($_ = isset($this->services['monolog.logger.cache']) ? $this->services['monolog.logger.cache'] : $this->get('monolog.logger.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'cache.system.recorder_inner' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\AdapterInterface
     */
    protected function getCache_System_RecorderInnerService($lazyLoad = true)
    {
        return $this->services['cache.system.recorder_inner'] = \Symfony\Component\Cache\Adapter\AbstractAdapter::createSystemCache('PKf3Ilb6Qh', 0, 'iMVxxS3A7m2OslZxnASj9m', (__DIR__.'/pools'), ${($_ = isset($this->services['monolog.logger.cache']) ? $this->services['monolog.logger.cache'] : $this->get('monolog.logger.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'cache.validator' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCache_ValidatorService()
    {
        return $this->services['cache.validator'] = new \Symfony\Component\Cache\Adapter\TraceableAdapter(${($_ = isset($this->services['cache.validator.recorder_inner']) ? $this->services['cache.validator.recorder_inner'] : $this->getCache_Validator_RecorderInnerService()) && false ?: '_'});
    }

    /**
     * Gets the private 'cache.validator.recorder_inner' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\AdapterInterface
     */
    protected function getCache_Validator_RecorderInnerService($lazyLoad = true)
    {
        return $this->services['cache.validator.recorder_inner'] = \Symfony\Component\Cache\Adapter\AbstractAdapter::createSystemCache('5cNJKRS0CQ', 0, 'iMVxxS3A7m2OslZxnASj9m', (__DIR__.'/pools'), ${($_ = isset($this->services['monolog.logger.cache']) ? $this->services['monolog.logger.cache'] : $this->get('monolog.logger.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'calllog_permission_voter' shared service.
     *
     * @return \Oleg\CallLogBundle\Security\Voter\CallLogPermissionVoter
     */
    protected function getCalllogPermissionVoterService()
    {
        return $this->services['calllog_permission_voter'] = new \Oleg\CallLogBundle\Security\Voter\CallLogPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'calllog_role_voter' shared service.
     *
     * @return \Oleg\CallLogBundle\Security\Voter\CallLogRoleVoter
     */
    protected function getCalllogRoleVoterService()
    {
        return $this->services['calllog_role_voter'] = new \Oleg\CallLogBundle\Security\Voter\CallLogRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'console.error_listener' shared service.
     *
     * @return \Symfony\Component\Console\EventListener\ErrorListener
     */
    protected function getConsole_ErrorListenerService()
    {
        return $this->services['console.error_listener'] = new \Symfony\Component\Console\EventListener\ErrorListener(${($_ = isset($this->services['monolog.logger.console']) ? $this->services['monolog.logger.console'] : $this->get('monolog.logger.console', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'controller_name_converter' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser
     */
    protected function getControllerNameConverterService()
    {
        return $this->services['controller_name_converter'] = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel')) && false ?: '_'});
    }

    /**
     * Gets the private 'debug.file_link_formatter' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Debug\FileLinkFormatter
     */
    protected function getDebug_FileLinkFormatterService()
    {
        return $this->services['debug.file_link_formatter'] = new \Symfony\Component\HttpKernel\Debug\FileLinkFormatter(NULL, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, $this->targetDirs[3], '/_profiler/open?file=%f&line=%l#line%l');
    }

    /**
     * Gets the private 'debug.log_processor' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Processor\DebugProcessor
     */
    protected function getDebug_LogProcessorService()
    {
        return $this->services['debug.log_processor'] = new \Symfony\Bridge\Monolog\Processor\DebugProcessor();
    }

    /**
     * Gets the private 'debug.security.access.decision_manager' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\TraceableAccessDecisionManager
     */
    protected function getDebug_Security_Access_DecisionManagerService()
    {
        return $this->services['debug.security.access.decision_manager'] = new \Symfony\Component\Security\Core\Authorization\TraceableAccessDecisionManager(new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager(new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['security.access.authenticated_voter']) ? $this->services['security.access.authenticated_voter'] : $this->getSecurity_Access_AuthenticatedVoterService()) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['security.access.role_hierarchy_voter']) ? $this->services['security.access.role_hierarchy_voter'] : $this->getSecurity_Access_RoleHierarchyVoterService()) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['security.access.expression_voter']) ? $this->services['security.access.expression_voter'] : $this->getSecurity_Access_ExpressionVoterService()) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['user_role_voter']) ? $this->services['user_role_voter'] : $this->getUserRoleVoterService()) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['user_permission_voter']) ? $this->services['user_permission_voter'] : $this->getUserPermissionVoterService()) && false ?: '_'};
            yield 5 => ${($_ = isset($this->services['scan_role_voter']) ? $this->services['scan_role_voter'] : $this->getScanRoleVoterService()) && false ?: '_'};
            yield 6 => ${($_ = isset($this->services['scan_permission_voter']) ? $this->services['scan_permission_voter'] : $this->getScanPermissionVoterService()) && false ?: '_'};
            yield 7 => ${($_ = isset($this->services['fellapp_role_voter']) ? $this->services['fellapp_role_voter'] : $this->getFellappRoleVoterService()) && false ?: '_'};
            yield 8 => ${($_ = isset($this->services['fellapp_permission_voter']) ? $this->services['fellapp_permission_voter'] : $this->getFellappPermissionVoterService()) && false ?: '_'};
            yield 9 => ${($_ = isset($this->services['vacreq_role_voter']) ? $this->services['vacreq_role_voter'] : $this->getVacreqRoleVoterService()) && false ?: '_'};
            yield 10 => ${($_ = isset($this->services['vacreq_permission_voter']) ? $this->services['vacreq_permission_voter'] : $this->getVacreqPermissionVoterService()) && false ?: '_'};
            yield 11 => ${($_ = isset($this->services['deidentifier_role_voter']) ? $this->services['deidentifier_role_voter'] : $this->getDeidentifierRoleVoterService()) && false ?: '_'};
            yield 12 => ${($_ = isset($this->services['deidentifier_permission_voter']) ? $this->services['deidentifier_permission_voter'] : $this->getDeidentifierPermissionVoterService()) && false ?: '_'};
            yield 13 => ${($_ = isset($this->services['calllog_role_voter']) ? $this->services['calllog_role_voter'] : $this->getCalllogRoleVoterService()) && false ?: '_'};
            yield 14 => ${($_ = isset($this->services['calllog_permission_voter']) ? $this->services['calllog_permission_voter'] : $this->getCalllogPermissionVoterService()) && false ?: '_'};
            yield 15 => ${($_ = isset($this->services['translationalresearch_role_voter']) ? $this->services['translationalresearch_role_voter'] : $this->getTranslationalresearchRoleVoterService()) && false ?: '_'};
            yield 16 => ${($_ = isset($this->services['translationalresearch_permission_voter']) ? $this->services['translationalresearch_permission_voter'] : $this->getTranslationalresearchPermissionVoterService()) && false ?: '_'};
        }, 17), 'affirmative', false, true));
    }

    /**
     * Gets the private 'deidentifier_permission_voter' shared service.
     *
     * @return \Oleg\DeidentifierBundle\Security\Voter\DeidentifierPermissionVoter
     */
    protected function getDeidentifierPermissionVoterService()
    {
        return $this->services['deidentifier_permission_voter'] = new \Oleg\DeidentifierBundle\Security\Voter\DeidentifierPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'deidentifier_role_voter' shared service.
     *
     * @return \Oleg\DeidentifierBundle\Security\Voter\DeidentifierRoleVoter
     */
    protected function getDeidentifierRoleVoterService()
    {
        return $this->services['deidentifier_role_voter'] = new \Oleg\DeidentifierBundle\Security\Voter\DeidentifierRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'doctrine.dbal.logger' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\Logger\DbalLogger
     */
    protected function getDoctrine_Dbal_LoggerService()
    {
        return $this->services['doctrine.dbal.logger'] = new \Symfony\Bridge\Doctrine\Logger\DbalLogger(${($_ = isset($this->services['monolog.logger.doctrine']) ? $this->services['monolog.logger.doctrine'] : $this->get('monolog.logger.doctrine', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : $this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'doctrine.dbal.logger.profiling.aperio' shared service.
     *
     * @return \Doctrine\DBAL\Logging\DebugStack
     */
    protected function getDoctrine_Dbal_Logger_Profiling_AperioService()
    {
        return $this->services['doctrine.dbal.logger.profiling.aperio'] = new \Doctrine\DBAL\Logging\DebugStack();
    }

    /**
     * Gets the private 'doctrine.dbal.logger.profiling.default' shared service.
     *
     * @return \Doctrine\DBAL\Logging\DebugStack
     */
    protected function getDoctrine_Dbal_Logger_Profiling_DefaultService()
    {
        return $this->services['doctrine.dbal.logger.profiling.default'] = new \Doctrine\DBAL\Logging\DebugStack();
    }

    /**
     * Gets the private 'doctrine.orm.naming_strategy.default' shared service.
     *
     * @return \Doctrine\ORM\Mapping\DefaultNamingStrategy
     */
    protected function getDoctrine_Orm_NamingStrategy_DefaultService()
    {
        return $this->services['doctrine.orm.naming_strategy.default'] = new \Doctrine\ORM\Mapping\DefaultNamingStrategy();
    }

    /**
     * Gets the private 'doctrine.orm.quote_strategy.default' shared service.
     *
     * @return \Doctrine\ORM\Mapping\DefaultQuoteStrategy
     */
    protected function getDoctrine_Orm_QuoteStrategy_DefaultService()
    {
        return $this->services['doctrine.orm.quote_strategy.default'] = new \Doctrine\ORM\Mapping\DefaultQuoteStrategy();
    }

    /**
     * Gets the private 'fellapp_permission_voter' shared service.
     *
     * @return \Oleg\FellAppBundle\Security\Voter\FellAppPermissionVoter
     */
    protected function getFellappPermissionVoterService()
    {
        return $this->services['fellapp_permission_voter'] = new \Oleg\FellAppBundle\Security\Voter\FellAppPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'fellapp_role_voter' shared service.
     *
     * @return \Oleg\FellAppBundle\Security\Voter\FellAppRoleVoter
     */
    protected function getFellappRoleVoterService()
    {
        return $this->services['fellapp_role_voter'] = new \Oleg\FellAppBundle\Security\Voter\FellAppRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'form.server_params' shared service.
     *
     * @return \Symfony\Component\Form\Util\ServerParams
     */
    protected function getForm_ServerParamsService()
    {
        return $this->services['form.server_params'] = new \Symfony\Component\Form\Util\ServerParams(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack')) && false ?: '_'});
    }

    /**
     * Gets the private 'form.type.choice' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\ChoiceType
     */
    protected function getForm_Type_ChoiceService()
    {
        return $this->services['form.type.choice'] = new \Symfony\Component\Form\Extension\Core\Type\ChoiceType(new \Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator(new \Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator(new \Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory(), ${($_ = isset($this->services['property_accessor']) ? $this->services['property_accessor'] : $this->get('property_accessor')) && false ?: '_'})));
    }

    /**
     * Gets the private 'form.type.form' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\FormType
     */
    protected function getForm_Type_FormService()
    {
        return $this->services['form.type.form'] = new \Symfony\Component\Form\Extension\Core\Type\FormType(${($_ = isset($this->services['property_accessor']) ? $this->services['property_accessor'] : $this->get('property_accessor')) && false ?: '_'});
    }

    /**
     * Gets the private 'form.type_extension.csrf' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension
     */
    protected function getForm_TypeExtension_CsrfService()
    {
        return $this->services['form.type_extension.csrf'] = new \Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension(${($_ = isset($this->services['security.csrf.token_manager']) ? $this->services['security.csrf.token_manager'] : $this->get('security.csrf.token_manager')) && false ?: '_'}, true, '_token', ${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->get('translator')) && false ?: '_'}, 'validators', ${($_ = isset($this->services['form.server_params']) ? $this->services['form.server_params'] : $this->getForm_ServerParamsService()) && false ?: '_'});
    }

    /**
     * Gets the private 'form.type_extension.form.data_collector' shared service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\Type\DataCollectorTypeExtension
     */
    protected function getForm_TypeExtension_Form_DataCollectorService()
    {
        return $this->services['form.type_extension.form.data_collector'] = new \Symfony\Component\Form\Extension\DataCollector\Type\DataCollectorTypeExtension(${($_ = isset($this->services['data_collector.form']) ? $this->services['data_collector.form'] : $this->get('data_collector.form')) && false ?: '_'});
    }

    /**
     * Gets the private 'form.type_extension.form.http_foundation' shared service.
     *
     * @return \Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension
     */
    protected function getForm_TypeExtension_Form_HttpFoundationService()
    {
        return $this->services['form.type_extension.form.http_foundation'] = new \Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler(${($_ = isset($this->services['form.server_params']) ? $this->services['form.server_params'] : $this->getForm_ServerParamsService()) && false ?: '_'}));
    }

    /**
     * Gets the private 'form.type_extension.form.validator' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension
     */
    protected function getForm_TypeExtension_Form_ValidatorService()
    {
        return $this->services['form.type_extension.form.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension(${($_ = isset($this->services['validator']) ? $this->services['validator'] : $this->get('validator')) && false ?: '_'});
    }

    /**
     * Gets the private 'form.type_extension.repeated.validator' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension
     */
    protected function getForm_TypeExtension_Repeated_ValidatorService()
    {
        return $this->services['form.type_extension.repeated.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension();
    }

    /**
     * Gets the private 'form.type_extension.submit.validator' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension
     */
    protected function getForm_TypeExtension_Submit_ValidatorService()
    {
        return $this->services['form.type_extension.submit.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension();
    }

    /**
     * Gets the private 'form.type_extension.upload.validator' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\UploadValidatorExtension
     */
    protected function getForm_TypeExtension_Upload_ValidatorService()
    {
        return $this->services['form.type_extension.upload.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\UploadValidatorExtension(${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->get('translator')) && false ?: '_'}, 'validators');
    }

    /**
     * Gets the private 'form.type_guesser.validator' shared service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser
     */
    protected function getForm_TypeGuesser_ValidatorService()
    {
        return $this->services['form.type_guesser.validator'] = new \Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser(${($_ = isset($this->services['validator']) ? $this->services['validator'] : $this->get('validator')) && false ?: '_'});
    }

    /**
     * Gets the private 'fos_user.user_listener' shared service.
     *
     * @return \FOS\UserBundle\Doctrine\UserListener
     */
    protected function getFosUser_UserListenerService()
    {
        return $this->services['fos_user.user_listener'] = new \FOS\UserBundle\Doctrine\UserListener(${($_ = isset($this->services['fos_user.util.password_updater']) ? $this->services['fos_user.util.password_updater'] : $this->getFosUser_Util_PasswordUpdaterService()) && false ?: '_'}, ${($_ = isset($this->services['fos_user.util.canonical_fields_updater']) ? $this->services['fos_user.util.canonical_fields_updater'] : $this->getFosUser_Util_CanonicalFieldsUpdaterService()) && false ?: '_'});
    }

    /**
     * Gets the private 'fos_user.user_provider.username' shared service.
     *
     * @return \FOS\UserBundle\Security\UserProvider
     */
    protected function getFosUser_UserProvider_UsernameService()
    {
        return $this->services['fos_user.user_provider.username'] = new \FOS\UserBundle\Security\UserProvider(${($_ = isset($this->services['fos_user.user_manager']) ? $this->services['fos_user.user_manager'] : $this->get('fos_user.user_manager')) && false ?: '_'});
    }

    /**
     * Gets the private 'fos_user.util.canonical_fields_updater' shared service.
     *
     * @return \FOS\UserBundle\Util\CanonicalFieldsUpdater
     */
    protected function getFosUser_Util_CanonicalFieldsUpdaterService()
    {
        $a = ${($_ = isset($this->services['fos_user.util.email_canonicalizer']) ? $this->services['fos_user.util.email_canonicalizer'] : $this->get('fos_user.util.email_canonicalizer')) && false ?: '_'};

        return $this->services['fos_user.util.canonical_fields_updater'] = new \FOS\UserBundle\Util\CanonicalFieldsUpdater($a, $a);
    }

    /**
     * Gets the private 'fos_user.util.password_updater' shared service.
     *
     * @return \FOS\UserBundle\Util\PasswordUpdater
     */
    protected function getFosUser_Util_PasswordUpdaterService()
    {
        return $this->services['fos_user.util.password_updater'] = new \FOS\UserBundle\Util\PasswordUpdater(${($_ = isset($this->services['security.encoder_factory']) ? $this->services['security.encoder_factory'] : $this->get('security.encoder_factory')) && false ?: '_'});
    }

    /**
     * Gets the private 'monolog.processor.psr_log_message' shared service.
     *
     * @return \Monolog\Processor\PsrLogMessageProcessor
     */
    protected function getMonolog_Processor_PsrLogMessageService()
    {
        return $this->services['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor();
    }

    /**
     * Gets the private 'oneup_uploader.error_handler.dropzone' shared service.
     *
     * @return \Oneup\UploaderBundle\Uploader\ErrorHandler\DropzoneErrorHandler
     */
    protected function getOneupUploader_ErrorHandler_DropzoneService()
    {
        return $this->services['oneup_uploader.error_handler.dropzone'] = new \Oneup\UploaderBundle\Uploader\ErrorHandler\DropzoneErrorHandler();
    }

    /**
     * Gets the private 'resolve_controller_name_subscriber' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\EventListener\ResolveControllerNameSubscriber
     */
    protected function getResolveControllerNameSubscriberService()
    {
        return $this->services['resolve_controller_name_subscriber'] = new \Symfony\Bundle\FrameworkBundle\EventListener\ResolveControllerNameSubscriber(${($_ = isset($this->services['controller_name_converter']) ? $this->services['controller_name_converter'] : $this->getControllerNameConverterService()) && false ?: '_'});
    }

    /**
     * Gets the private 'router.request_context' shared service.
     *
     * @return \Symfony\Component\Routing\RequestContext
     */
    protected function getRouter_RequestContextService()
    {
        return $this->services['router.request_context'] = new \Symfony\Component\Routing\RequestContext('', 'GET', 'localhost', 'http', 80, 443);
    }

    /**
     * Gets the private 'scan_permission_voter' shared service.
     *
     * @return \Oleg\OrderformBundle\Security\Voter\ScanPermissionVoter
     */
    protected function getScanPermissionVoterService()
    {
        return $this->services['scan_permission_voter'] = new \Oleg\OrderformBundle\Security\Voter\ScanPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'scan_role_voter' shared service.
     *
     * @return \Oleg\OrderformBundle\Security\Voter\ScanRoleVoter
     */
    protected function getScanRoleVoterService()
    {
        return $this->services['scan_role_voter'] = new \Oleg\OrderformBundle\Security\Voter\ScanRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'security.access.authenticated_voter' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter
     */
    protected function getSecurity_Access_AuthenticatedVoterService()
    {
        return $this->services['security.access.authenticated_voter'] = new \Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter(${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'});
    }

    /**
     * Gets the private 'security.access.expression_voter' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter
     */
    protected function getSecurity_Access_ExpressionVoterService()
    {
        return $this->services['security.access.expression_voter'] = new \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter(new \Symfony\Component\Security\Core\Authorization\ExpressionLanguage(), ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'}, ${($_ = isset($this->services['security.role_hierarchy']) ? $this->services['security.role_hierarchy'] : $this->getSecurity_RoleHierarchyService()) && false ?: '_'});
    }

    /**
     * Gets the private 'security.access.role_hierarchy_voter' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter
     */
    protected function getSecurity_Access_RoleHierarchyVoterService()
    {
        return $this->services['security.access.role_hierarchy_voter'] = new \Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter(${($_ = isset($this->services['security.role_hierarchy']) ? $this->services['security.role_hierarchy'] : $this->getSecurity_RoleHierarchyService()) && false ?: '_'});
    }

    /**
     * Gets the private 'security.access_listener' shared service.
     *
     * @return \Symfony\Component\Security\Http\Firewall\AccessListener
     */
    protected function getSecurity_AccessListenerService()
    {
        return $this->services['security.access_listener'] = new \Symfony\Component\Security\Http\Firewall\AccessListener(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['security.access_map']) ? $this->services['security.access_map'] : $this->getSecurity_AccessMapService()) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'});
    }

    /**
     * Gets the private 'security.access_map' shared service.
     *
     * @return \Symfony\Component\Security\Http\AccessMap
     */
    protected function getSecurity_AccessMapService()
    {
        $a = new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/event-log');

        $b = new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/incoming-scan-orders');

        $this->services['security.access_map'] = $instance = new \Symfony\Component\Security\Http\AccessMap();

        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/admin/first-time-login-generation-init/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/first-time-user-generation-init/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/first-time-user-generation-init/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/access-requests/change-status/'), array(0 => 'ROLE_EDITOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/common/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/util/common/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/file-download/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/file-delete/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/user/only-ajax/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/formnode-fields/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add($a, array(0 => 'ROLE_USERDIRECTORY_EDITOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/users/previous'), array(0 => 'ROLE_USERDIRECTORY_EDITOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/users/generate'), array(0 => 'ROLE_USERDIRECTORY_EDITOR'), NULL);
        $instance->add($a, array(0 => 'ROLE_USERDIRECTORY_EDITOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/admin/populate-all-lists-with-default-values'), array(0 => 'ROLE_USERDIRECTORY_ADMIN'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/admin/'), array(0 => 'ROLE_USERDIRECTORY_ADMIN'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/settings/'), array(0 => 'ROLE_PLATFORM_DEPUTY_ADMIN'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/tree-util/common/composition-tree/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory/'), array(0 => 'ROLE_USERDIRECTORY_OBSERVER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/admin/populate-all-lists-with-default-values'), array(0 => 'ROLE_SCANORDER_ADMIN'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/users/generate'), array(0 => 'ROLE_SCANORDER_ADMIN'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/users/previous'), array(0 => 'ROLE_SCANORDER_ADMIN'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/admin/'), array(0 => 'ROLE_SCANORDER_PROCESSOR'), NULL);
        $instance->add($b, array(0 => 'ROLE_SCANORDER_PROCESSOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/settings/'), array(0 => 'ROLE_PLATFORM_DEPUTY_ADMIN'), NULL);
        $instance->add($b, array(0 => 'ROLE_SCANORDER_PROCESSOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/event-log'), array(0 => 'ROLE_SCANORDER_PROCESSOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/incoming-slide-return-requests'), array(0 => 'ROLE_SCANORDER_PROCESSOR'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/util/'), array(0 => 'ROLE_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/check/'), array(0 => 'ROLE_SCANORDER_SUBMITTER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/scan-order'), array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/my-scan-orders'), array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/slide-return-request'), array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/my-slide-return-requests'), array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/image-viewer/'), array(0 => 'ROLE_SCANORDER_SUBMITTER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan/'), array(0 => new \Symfony\Component\ExpressionLanguage\SerializedParsedExpression('has_role(\'ROLE_SCANORDER_SUBMITTER\') and !has_role(\'ROLE_SCANORDER_UNAPPROVED\') and !has_role(\'ROLE_SCANORDER_BANNED\')', 'O:52:"Symfony\\Component\\ExpressionLanguage\\Node\\BinaryNode":2:{s:5:"nodes";a:2:{s:4:"left";O:52:"Symfony\\Component\\ExpressionLanguage\\Node\\BinaryNode":2:{s:5:"nodes";a:2:{s:4:"left";O:54:"Symfony\\Component\\ExpressionLanguage\\Node\\FunctionNode":2:{s:5:"nodes";a:1:{s:9:"arguments";O:46:"Symfony\\Component\\ExpressionLanguage\\Node\\Node":2:{s:5:"nodes";a:1:{i:0;O:54:"Symfony\\Component\\ExpressionLanguage\\Node\\ConstantNode":3:{s:68:"' . "\0" . 'Symfony\\Component\\ExpressionLanguage\\Node\\ConstantNode' . "\0" . 'isIdentifier";b:0;s:5:"nodes";a:0:{}s:10:"attributes";a:1:{s:5:"value";s:24:"ROLE_SCANORDER_SUBMITTER";}}}s:10:"attributes";a:0:{}}}s:10:"attributes";a:1:{s:4:"name";s:8:"has_role";}}s:5:"right";O:51:"Symfony\\Component\\ExpressionLanguage\\Node\\UnaryNode":2:{s:5:"nodes";a:1:{s:4:"node";O:54:"Symfony\\Component\\ExpressionLanguage\\Node\\FunctionNode":2:{s:5:"nodes";a:1:{s:9:"arguments";O:46:"Symfony\\Component\\ExpressionLanguage\\Node\\Node":2:{s:5:"nodes";a:1:{i:0;O:54:"Symfony\\Component\\ExpressionLanguage\\Node\\ConstantNode":3:{s:68:"' . "\0" . 'Symfony\\Component\\ExpressionLanguage\\Node\\ConstantNode' . "\0" . 'isIdentifier";b:0;s:5:"nodes";a:0:{}s:10:"attributes";a:1:{s:5:"value";s:25:"ROLE_SCANORDER_UNAPPROVED";}}}s:10:"attributes";a:0:{}}}s:10:"attributes";a:1:{s:4:"name";s:8:"has_role";}}}s:10:"attributes";a:1:{s:8:"operator";s:1:"!";}}}s:10:"attributes";a:1:{s:8:"operator";s:3:"and";}}s:5:"right";O:51:"Symfony\\Component\\ExpressionLanguage\\Node\\UnaryNode":2:{s:5:"nodes";a:1:{s:4:"node";O:54:"Symfony\\Component\\ExpressionLanguage\\Node\\FunctionNode":2:{s:5:"nodes";a:1:{s:9:"arguments";O:46:"Symfony\\Component\\ExpressionLanguage\\Node\\Node":2:{s:5:"nodes";a:1:{i:0;O:54:"Symfony\\Component\\ExpressionLanguage\\Node\\ConstantNode":3:{s:68:"' . "\0" . 'Symfony\\Component\\ExpressionLanguage\\Node\\ConstantNode' . "\0" . 'isIdentifier";b:0;s:5:"nodes";a:0:{}s:10:"attributes";a:1:{s:5:"value";s:21:"ROLE_SCANORDER_BANNED";}}}s:10:"attributes";a:0:{}}}s:10:"attributes";a:1:{s:4:"name";s:8:"has_role";}}}s:10:"attributes";a:1:{s:8:"operator";s:1:"!";}}}s:10:"attributes";a:1:{s:8:"operator";s:3:"and";}}')), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/download/', NULL, array(), array(0 => '127.0.0.1', 1 => '::1')), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/download/'), array(0 => 'ROLE_FELLAPP_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications/'), array(0 => 'ROLE_FELLAPP_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/deidentifier/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/deidentifier/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/deidentifier/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/deidentifier/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/deidentifier/'), array(0 => 'ROLE_DEIDENTIFICATOR_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/vacation-request/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/vacation-request/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/vacation-request/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/vacation-request/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/vacation-request/'), array(0 => 'ROLE_VACREQ_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/call-log-book/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/call-log-book/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/call-log-book/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/call-log-book/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/call-log-book/'), array(0 => 'ROLE_CALLLOG_USER'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/translational-research/access-requests/new/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/translational-research/login'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/translational-research/setloginvisit'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/translational-research/account-requests/'), array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/translational-research/'), array(0 => 'ROLE_TRANSLATIONALRESEARCH_USER'), NULL);

        return $instance;
    }

    /**
     * Gets the private 'security.authentication.manager' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager
     */
    protected function getSecurity_Authentication_ManagerService()
    {
        $this->services['security.authentication.manager'] = $instance = new \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager(new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['security.authentication.provider.simple_form.aperio_ldap_firewall']) ? $this->services['security.authentication.provider.simple_form.aperio_ldap_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_AperioLdapFirewallService()) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['security.authentication.provider.rememberme.aperio_ldap_firewall']) ? $this->services['security.authentication.provider.rememberme.aperio_ldap_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_AperioLdapFirewallService()) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['security.authentication.provider.anonymous.aperio_ldap_firewall']) ? $this->services['security.authentication.provider.anonymous.aperio_ldap_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_AperioLdapFirewallService()) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['security.authentication.provider.simple_form.ldap_employees_firewall']) ? $this->services['security.authentication.provider.simple_form.ldap_employees_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_LdapEmployeesFirewallService()) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['security.authentication.provider.rememberme.ldap_employees_firewall']) ? $this->services['security.authentication.provider.rememberme.ldap_employees_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_LdapEmployeesFirewallService()) && false ?: '_'};
            yield 5 => ${($_ = isset($this->services['security.authentication.provider.anonymous.ldap_employees_firewall']) ? $this->services['security.authentication.provider.anonymous.ldap_employees_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_LdapEmployeesFirewallService()) && false ?: '_'};
            yield 6 => ${($_ = isset($this->services['security.authentication.provider.simple_form.ldap_fellapp_firewall']) ? $this->services['security.authentication.provider.simple_form.ldap_fellapp_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_LdapFellappFirewallService()) && false ?: '_'};
            yield 7 => ${($_ = isset($this->services['security.authentication.provider.rememberme.ldap_fellapp_firewall']) ? $this->services['security.authentication.provider.rememberme.ldap_fellapp_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_LdapFellappFirewallService()) && false ?: '_'};
            yield 8 => ${($_ = isset($this->services['security.authentication.provider.anonymous.ldap_fellapp_firewall']) ? $this->services['security.authentication.provider.anonymous.ldap_fellapp_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_LdapFellappFirewallService()) && false ?: '_'};
            yield 9 => ${($_ = isset($this->services['security.authentication.provider.simple_form.ldap_deidentifier_firewall']) ? $this->services['security.authentication.provider.simple_form.ldap_deidentifier_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_LdapDeidentifierFirewallService()) && false ?: '_'};
            yield 10 => ${($_ = isset($this->services['security.authentication.provider.rememberme.ldap_deidentifier_firewall']) ? $this->services['security.authentication.provider.rememberme.ldap_deidentifier_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_LdapDeidentifierFirewallService()) && false ?: '_'};
            yield 11 => ${($_ = isset($this->services['security.authentication.provider.anonymous.ldap_deidentifier_firewall']) ? $this->services['security.authentication.provider.anonymous.ldap_deidentifier_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_LdapDeidentifierFirewallService()) && false ?: '_'};
            yield 12 => ${($_ = isset($this->services['security.authentication.provider.simple_form.ldap_vacreq_firewall']) ? $this->services['security.authentication.provider.simple_form.ldap_vacreq_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_LdapVacreqFirewallService()) && false ?: '_'};
            yield 13 => ${($_ = isset($this->services['security.authentication.provider.rememberme.ldap_vacreq_firewall']) ? $this->services['security.authentication.provider.rememberme.ldap_vacreq_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_LdapVacreqFirewallService()) && false ?: '_'};
            yield 14 => ${($_ = isset($this->services['security.authentication.provider.anonymous.ldap_vacreq_firewall']) ? $this->services['security.authentication.provider.anonymous.ldap_vacreq_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_LdapVacreqFirewallService()) && false ?: '_'};
            yield 15 => ${($_ = isset($this->services['security.authentication.provider.simple_form.ldap_calllog_firewall']) ? $this->services['security.authentication.provider.simple_form.ldap_calllog_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_LdapCalllogFirewallService()) && false ?: '_'};
            yield 16 => ${($_ = isset($this->services['security.authentication.provider.rememberme.ldap_calllog_firewall']) ? $this->services['security.authentication.provider.rememberme.ldap_calllog_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_LdapCalllogFirewallService()) && false ?: '_'};
            yield 17 => ${($_ = isset($this->services['security.authentication.provider.anonymous.ldap_calllog_firewall']) ? $this->services['security.authentication.provider.anonymous.ldap_calllog_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_LdapCalllogFirewallService()) && false ?: '_'};
            yield 18 => ${($_ = isset($this->services['security.authentication.provider.simple_form.ldap_translationalresearch_firewall']) ? $this->services['security.authentication.provider.simple_form.ldap_translationalresearch_firewall'] : $this->getSecurity_Authentication_Provider_SimpleForm_LdapTranslationalresearchFirewallService()) && false ?: '_'};
            yield 19 => ${($_ = isset($this->services['security.authentication.provider.rememberme.ldap_translationalresearch_firewall']) ? $this->services['security.authentication.provider.rememberme.ldap_translationalresearch_firewall'] : $this->getSecurity_Authentication_Provider_Rememberme_LdapTranslationalresearchFirewallService()) && false ?: '_'};
            yield 20 => ${($_ = isset($this->services['security.authentication.provider.anonymous.ldap_translationalresearch_firewall']) ? $this->services['security.authentication.provider.anonymous.ldap_translationalresearch_firewall'] : $this->getSecurity_Authentication_Provider_Anonymous_LdapTranslationalresearchFirewallService()) && false ?: '_'};
        }, 21), true);

        $instance->setEventDispatcher(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher')) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.aperio_ldap_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_AperioLdapFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.aperio_ldap_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.ldap_calllog_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_LdapCalllogFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.ldap_calllog_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.ldap_deidentifier_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_LdapDeidentifierFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.ldap_deidentifier_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.ldap_employees_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_LdapEmployeesFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.ldap_employees_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.ldap_fellapp_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_LdapFellappFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.ldap_fellapp_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.ldap_translationalresearch_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_LdapTranslationalresearchFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.ldap_translationalresearch_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.anonymous.ldap_vacreq_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Anonymous_LdapVacreqFirewallService()
    {
        return $this->services['security.authentication.provider.anonymous.ldap_vacreq_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('599db05a161ef7.25162865');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.aperio_ldap_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_AperioLdapFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.aperio_ldap_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'aperio_ldap_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.ldap_calllog_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_LdapCalllogFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.ldap_calllog_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_calllog_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.ldap_deidentifier_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_LdapDeidentifierFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.ldap_deidentifier_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_deidentifier_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.ldap_employees_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_LdapEmployeesFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.ldap_employees_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_employees_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.ldap_fellapp_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_LdapFellappFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.ldap_fellapp_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_fellapp_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.ldap_translationalresearch_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_LdapTranslationalresearchFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.ldap_translationalresearch_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_translationalresearch_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.rememberme.ldap_vacreq_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_Rememberme_LdapVacreqFirewallService()
    {
        return $this->services['security.authentication.provider.rememberme.ldap_vacreq_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider(${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : $this->getSecurity_UserCheckerService()) && false ?: '_'}, '563fd817cf2c4f1f692d90650b6fba50f782ccc9', 'ldap_vacreq_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.aperio_ldap_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_AperioLdapFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.aperio_ldap_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'aperio_ldap_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.ldap_calllog_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_LdapCalllogFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.ldap_calllog_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'ldap_calllog_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.ldap_deidentifier_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_LdapDeidentifierFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.ldap_deidentifier_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'ldap_deidentifier_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.ldap_employees_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_LdapEmployeesFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.ldap_employees_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'ldap_employees_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.ldap_fellapp_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_LdapFellappFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.ldap_fellapp_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'ldap_fellapp_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.ldap_translationalresearch_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_LdapTranslationalresearchFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.ldap_translationalresearch_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'ldap_translationalresearch_firewall');
    }

    /**
     * Gets the private 'security.authentication.provider.simple_form.ldap_vacreq_firewall' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider
     */
    protected function getSecurity_Authentication_Provider_SimpleForm_LdapVacreqFirewallService()
    {
        return $this->services['security.authentication.provider.simple_form.ldap_vacreq_firewall'] = new \Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider(${($_ = isset($this->services['custom_authenticator']) ? $this->services['custom_authenticator'] : $this->get('custom_authenticator')) && false ?: '_'}, ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}, 'ldap_vacreq_firewall');
    }

    /**
     * Gets the private 'security.authentication.session_strategy' shared service.
     *
     * @return \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy
     */
    protected function getSecurity_Authentication_SessionStrategyService()
    {
        return $this->services['security.authentication.session_strategy'] = new \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy('migrate');
    }

    /**
     * Gets the private 'security.authentication.trust_resolver' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver
     */
    protected function getSecurity_Authentication_TrustResolverService()
    {
        return $this->services['security.authentication.trust_resolver'] = new \Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver('Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken', 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken');
    }

    /**
     * Gets the private 'security.channel_listener' shared service.
     *
     * @return \Symfony\Component\Security\Http\Firewall\ChannelListener
     */
    protected function getSecurity_ChannelListenerService()
    {
        return $this->services['security.channel_listener'] = new \Symfony\Component\Security\Http\Firewall\ChannelListener(${($_ = isset($this->services['security.access_map']) ? $this->services['security.access_map'] : $this->getSecurity_AccessMapService()) && false ?: '_'}, new \Symfony\Component\Security\Http\EntryPoint\RetryAuthenticationEntryPoint(80, 443), ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});
    }

    /**
     * Gets the private 'security.context_listener.0' shared service.
     *
     * @return \Symfony\Component\Security\Http\Firewall\ContextListener
     */
    protected function getSecurity_ContextListener_0Service()
    {
        return $this->services['security.context_listener.0'] = new \Symfony\Component\Security\Http\Firewall\ContextListener(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'}, array(0 => ${($_ = isset($this->services['fos_user.user_provider.username']) ? $this->services['fos_user.user_provider.username'] : $this->getFosUser_UserProvider_UsernameService()) && false ?: '_'}), 'scan_auth', ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : $this->getSecurity_Authentication_TrustResolverService()) && false ?: '_'});
    }

    /**
     * Gets the private 'security.firewall.map' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallMap
     */
    protected function getSecurity_Firewall_MapService()
    {
        return $this->services['security.firewall.map'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallMap(new \Symfony\Component\DependencyInjection\ServiceLocator(array('security.firewall.map.context.aperio_ldap_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.aperio_ldap_firewall']) ? $this->services['security.firewall.map.context.aperio_ldap_firewall'] : $this->get('security.firewall.map.context.aperio_ldap_firewall')) && false ?: '_'};
        }, 'security.firewall.map.context.ldap_calllog_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.ldap_calllog_firewall']) ? $this->services['security.firewall.map.context.ldap_calllog_firewall'] : $this->get('security.firewall.map.context.ldap_calllog_firewall')) && false ?: '_'};
        }, 'security.firewall.map.context.ldap_deidentifier_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.ldap_deidentifier_firewall']) ? $this->services['security.firewall.map.context.ldap_deidentifier_firewall'] : $this->get('security.firewall.map.context.ldap_deidentifier_firewall')) && false ?: '_'};
        }, 'security.firewall.map.context.ldap_employees_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.ldap_employees_firewall']) ? $this->services['security.firewall.map.context.ldap_employees_firewall'] : $this->get('security.firewall.map.context.ldap_employees_firewall')) && false ?: '_'};
        }, 'security.firewall.map.context.ldap_fellapp_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.ldap_fellapp_firewall']) ? $this->services['security.firewall.map.context.ldap_fellapp_firewall'] : $this->get('security.firewall.map.context.ldap_fellapp_firewall')) && false ?: '_'};
        }, 'security.firewall.map.context.ldap_translationalresearch_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.ldap_translationalresearch_firewall']) ? $this->services['security.firewall.map.context.ldap_translationalresearch_firewall'] : $this->get('security.firewall.map.context.ldap_translationalresearch_firewall')) && false ?: '_'};
        }, 'security.firewall.map.context.ldap_vacreq_firewall' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.ldap_vacreq_firewall']) ? $this->services['security.firewall.map.context.ldap_vacreq_firewall'] : $this->get('security.firewall.map.context.ldap_vacreq_firewall')) && false ?: '_'};
        })), new RewindableGenerator(function () {
            yield 'security.firewall.map.context.aperio_ldap_firewall' => ${($_ = isset($this->services['security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe']) ? $this->services['security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe'] : $this->getSecurity_RequestMatcher_Af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffeService()) && false ?: '_'};
            yield 'security.firewall.map.context.ldap_employees_firewall' => ${($_ = isset($this->services['security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5']) ? $this->services['security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5'] : $this->getSecurity_RequestMatcher_D9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5Service()) && false ?: '_'};
            yield 'security.firewall.map.context.ldap_fellapp_firewall' => ${($_ = isset($this->services['security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f']) ? $this->services['security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f'] : $this->getSecurity_RequestMatcher_Fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1fService()) && false ?: '_'};
            yield 'security.firewall.map.context.ldap_deidentifier_firewall' => ${($_ = isset($this->services['security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776']) ? $this->services['security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776'] : $this->getSecurity_RequestMatcher_Eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776Service()) && false ?: '_'};
            yield 'security.firewall.map.context.ldap_vacreq_firewall' => ${($_ = isset($this->services['security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e']) ? $this->services['security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e'] : $this->getSecurity_RequestMatcher_2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7eService()) && false ?: '_'};
            yield 'security.firewall.map.context.ldap_calllog_firewall' => ${($_ = isset($this->services['security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1']) ? $this->services['security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1'] : $this->getSecurity_RequestMatcher_Ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1Service()) && false ?: '_'};
            yield 'security.firewall.map.context.ldap_translationalresearch_firewall' => ${($_ = isset($this->services['security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee']) ? $this->services['security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee'] : $this->getSecurity_RequestMatcher_96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daeeService()) && false ?: '_'};
        }, 7));
    }

    /**
     * Gets the private 'security.http_utils' shared service.
     *
     * @return \Symfony\Component\Security\Http\HttpUtils
     */
    protected function getSecurity_HttpUtilsService()
    {
        $a = ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'};

        return $this->services['security.http_utils'] = new \Symfony\Component\Security\Http\HttpUtils($a, $a);
    }

    /**
     * Gets the private 'security.logout.handler.session' shared service.
     *
     * @return \Symfony\Component\Security\Http\Logout\SessionLogoutHandler
     */
    protected function getSecurity_Logout_Handler_SessionService()
    {
        return $this->services['security.logout.handler.session'] = new \Symfony\Component\Security\Http\Logout\SessionLogoutHandler();
    }

    /**
     * Gets the private 'security.logout_url_generator' shared service.
     *
     * @return \Symfony\Component\Security\Http\Logout\LogoutUrlGenerator
     */
    protected function getSecurity_LogoutUrlGeneratorService()
    {
        $this->services['security.logout_url_generator'] = $instance = new \Symfony\Component\Security\Http\Logout\LogoutUrlGenerator(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : $this->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['router']) ? $this->services['router'] : $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE)) && false ?: '_'});

        $instance->registerListener('aperio_ldap_firewall', '/scan/logout', 'logout', '_csrf_token', NULL, 'scan_auth');
        $instance->registerListener('ldap_employees_firewall', '/directory/logout', 'logout', '_csrf_token', NULL, 'scan_auth');
        $instance->registerListener('ldap_fellapp_firewall', '/fellowship-applications/logout', 'logout', '_csrf_token', NULL, 'scan_auth');
        $instance->registerListener('ldap_deidentifier_firewall', '/deidentifier/logout', 'logout', '_csrf_token', NULL, 'scan_auth');
        $instance->registerListener('ldap_vacreq_firewall', '/vacation-request/logout', 'logout', '_csrf_token', NULL, 'scan_auth');
        $instance->registerListener('ldap_calllog_firewall', '/call-log-book/logout', 'logout', '_csrf_token', NULL, 'scan_auth');
        $instance->registerListener('ldap_translationalresearch_firewall', '/translational-research/logout', 'logout', '_csrf_token', NULL, 'scan_auth');

        return $instance;
    }

    /**
     * Gets the private 'security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7eService()
    {
        return $this->services['security.request_matcher.2e7774ea8643f20654b9f9766d10b5a7e4e30949b8f6515aaf750bee94af0552c0357a7e'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/vacation-request');
    }

    /**
     * Gets the private 'security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daeeService()
    {
        return $this->services['security.request_matcher.96ccd314996e2c71ea102aedc6ff158f56f2b7ec4318c85fc557c7cf386aaecb4f46daee'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/translational-research');
    }

    /**
     * Gets the private 'security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_Ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1Service()
    {
        return $this->services['security.request_matcher.ac4312a145008eb3bdc9290b5cfd988e48568a24fcc473fd9b1e783d82bdf2c8976f61e1'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/call-log-book');
    }

    /**
     * Gets the private 'security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_Af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffeService()
    {
        return $this->services['security.request_matcher.af9c2aad6810a2aa6ea332019174c8f13a568e5b5f80c6cd4dca330d52920976c2362ffe'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/scan');
    }

    /**
     * Gets the private 'security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_D9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5Service()
    {
        return $this->services['security.request_matcher.d9003c1ca5c082eaad4d04defd4c303cad7d818ffe3fc3d50ab3d2c49285cc9a6e02e8b5'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/directory');
    }

    /**
     * Gets the private 'security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_Eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776Service()
    {
        return $this->services['security.request_matcher.eb65c0e7289d52824a1362c8e4edc184c601968399378645f35fc1b4ab6507a99ffee776'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/deidentifier');
    }

    /**
     * Gets the private 'security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestMatcher
     */
    protected function getSecurity_RequestMatcher_Fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1fService()
    {
        return $this->services['security.request_matcher.fb738556dc8f7c4aa3f0535906196c8e73edcbc00e5d09b907f4d1f85347be70fd54bd1f'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/fellowship-applications');
    }

    /**
     * Gets the private 'security.role_hierarchy' shared service.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleHierarchy
     */
    protected function getSecurity_RoleHierarchyService()
    {
        return $this->services['security.role_hierarchy'] = new \Symfony\Component\Security\Core\Role\RoleHierarchy(array('ROLE_SCANORDER_ALL_PATHOLOGY' => array(0 => 'ROLE_SCANORDER_PATHOLOGY_RESIDENT', 1 => 'ROLE_SCANORDER_PATHOLOGY_FELLOW', 2 => 'ROLE_SCANORDER_PATHOLOGY_FACULTY'), 'ROLE_SCANORDER_PATHOLOGY_RESIDENT' => array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), 'ROLE_SCANORDER_PATHOLOGY_FELLOW' => array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), 'ROLE_SCANORDER_PATHOLOGY_FACULTY' => array(0 => 'ROLE_SCANORDER_SUBMITTER', 1 => 'ROLE_SCANORDER_ORDERING_PROVIDER'), 'ROLE_SCANORDER_SUBMITTER' => array(0 => 'ROLE_USER'), 'ROLE_SCANORDER_ORDERING_PROVIDER' => array(0 => 'ROLE_USER'), 'ROLE_SCANORDER_PROCESSOR' => array(0 => 'ROLE_USER', 1 => 'ROLE_SCANORDER_SUBMITTER', 2 => 'ROLE_SCANORDER_ORDERING_PROVIDER', 3 => 'ROLE_SCANORDER_ALL_PATHOLOGY', 4 => 'ROLE_EDITOR'), 'ROLE_SCANORDER_ADMIN' => array(0 => 'ROLE_USER', 1 => 'ROLE_SCANORDER_PROCESSOR', 2 => 'ROLE_SCANORDER_SUBMITTER', 3 => 'ROLE_SCANORDER_ORDERING_PROVIDER', 4 => 'ROLE_SCANORDER_ALL_PATHOLOGY', 5 => 'ROLE_EDITOR'), 'ROLE_USERDIRECTORY_EDITOR' => array(0 => 'ROLE_EDITOR', 1 => 'ROLE_USER', 2 => 'ROLE_USERDIRECTORY_OBSERVER'), 'ROLE_USERDIRECTORY_ADMIN' => array(0 => 'ROLE_USER', 1 => 'ROLE_USERDIRECTORY_OBSERVER', 2 => 'ROLE_USERDIRECTORY_EDITOR'), 'ROLE_FELLAPP_ADMIN' => array(0 => 'ROLE_USER', 1 => 'ROLE_FELLAPP_OBSERVER', 2 => 'ROLE_USERDIRECTORY_EDITOR'), 'ROLE_FELLAPP_OBSERVER' => array(0 => 'ROLE_FELLAPP_USER'), 'ROLE_CALLLOG_PATHOLOGY_ATTENDING' => array(0 => 'ROLE_CALLLOG_USER'), 'ROLE_CALLLOG_PATHOLOGY_RESIDENT' => array(0 => 'ROLE_CALLLOG_USER'), 'ROLE_CALLLOG_PATHOLOGY_FELLOW' => array(0 => 'ROLE_CALLLOG_USER'), 'ROLE_CALLLOG_DATA_QUALITY' => array(0 => 'ROLE_CALLLOG_USER'), 'ROLE_CALLLOG_ADMIN' => array(0 => 'ROLE_CALLLOG_USER', 1 => 'ROLE_CALLLOG_PATHOLOGY_ATTENDING', 2 => 'ROLE_CALLLOG_PATHOLOGY_RESIDENT', 3 => 'ROLE_CALLLOG_PATHOLOGY_FELLOW', 4 => 'ROLE_CALLLOG_DATA_QUALITY', 5 => 'ROLE_EDITOR'), 'ROLE_TRANSLATIONALRESEARCH_ADMIN' => array(0 => 'ROLE_TRANSLATIONALRESEARCH_USER', 1 => 'ROLE_EDITOR'), 'ROLE_PLATFORM_DEPUTY_ADMIN' => array(0 => 'ROLE_ALLOWED_TO_SWITCH', 1 => 'ROLE_USERDIRECTORY_ADMIN', 2 => 'ROLE_SCANORDER_ADMIN', 3 => 'ROLE_FELLAPP_ADMIN', 4 => 'ROLE_DEIDENTIFICATOR_ADMIN', 5 => 'ROLE_VACREQ_ADMIN', 6 => 'ROLE_CALLLOG_ADMIN', 7 => 'ROLE_TRANSLATIONALRESEARCH_ADMIN'), 'ROLE_PLATFORM_ADMIN' => array(0 => 'ROLE_PLATFORM_DEPUTY_ADMIN')));
    }

    /**
     * Gets the private 'security.user_checker' shared service.
     *
     * @return \Symfony\Component\Security\Core\User\UserChecker
     */
    protected function getSecurity_UserCheckerService()
    {
        return $this->services['security.user_checker'] = new \Symfony\Component\Security\Core\User\UserChecker();
    }

    /**
     * Gets the private 'security.user_value_resolver' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\SecurityUserValueResolver
     */
    protected function getSecurity_UserValueResolverService()
    {
        return $this->services['security.user_value_resolver'] = new \Symfony\Bundle\SecurityBundle\SecurityUserValueResolver(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : $this->get('security.token_storage')) && false ?: '_'});
    }

    /**
     * Gets the private 'service_locator.e64d23c3bf770e2cf44b71643280668d' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator
     */
    protected function getServiceLocator_E64d23c3bf770e2cf44b71643280668dService()
    {
        return $this->services['service_locator.e64d23c3bf770e2cf44b71643280668d'] = new \Symfony\Component\DependencyInjection\ServiceLocator(array('esi' => function () {
            return ${($_ = isset($this->services['fragment.renderer.esi']) ? $this->services['fragment.renderer.esi'] : $this->get('fragment.renderer.esi')) && false ?: '_'};
        }, 'hinclude' => function () {
            return ${($_ = isset($this->services['fragment.renderer.hinclude']) ? $this->services['fragment.renderer.hinclude'] : $this->get('fragment.renderer.hinclude')) && false ?: '_'};
        }, 'inline' => function () {
            return ${($_ = isset($this->services['fragment.renderer.inline']) ? $this->services['fragment.renderer.inline'] : $this->get('fragment.renderer.inline')) && false ?: '_'};
        }, 'ssi' => function () {
            return ${($_ = isset($this->services['fragment.renderer.ssi']) ? $this->services['fragment.renderer.ssi'] : $this->get('fragment.renderer.ssi')) && false ?: '_'};
        }));
    }

    /**
     * Gets the private 'session.storage.metadata_bag' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    protected function getSession_Storage_MetadataBagService()
    {
        return $this->services['session.storage.metadata_bag'] = new \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag('_sf2_meta', '0');
    }

    /**
     * Gets the private 'swiftmailer.mailer.default.transport.eventdispatcher' shared service.
     *
     * @return \Swift_Events_SimpleEventDispatcher
     */
    protected function getSwiftmailer_Mailer_Default_Transport_EventdispatcherService()
    {
        return $this->services['swiftmailer.mailer.default.transport.eventdispatcher'] = new \Swift_Events_SimpleEventDispatcher();
    }

    /**
     * Gets the private 'templating.locator' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator
     */
    protected function getTemplating_LocatorService()
    {
        return $this->services['templating.locator'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator(${($_ = isset($this->services['file_locator']) ? $this->services['file_locator'] : $this->get('file_locator')) && false ?: '_'}, __DIR__);
    }

    /**
     * Gets the private 'translationalresearch_permission_voter' shared service.
     *
     * @return \Oleg\TranslationalResearchBundle\Security\Voter\TranslationalResearchPermissionVoter
     */
    protected function getTranslationalresearchPermissionVoterService()
    {
        return $this->services['translationalresearch_permission_voter'] = new \Oleg\TranslationalResearchBundle\Security\Voter\TranslationalResearchPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'translationalresearch_role_voter' shared service.
     *
     * @return \Oleg\TranslationalResearchBundle\Security\Voter\TranslationalResearchRoleVoter
     */
    protected function getTranslationalresearchRoleVoterService()
    {
        return $this->services['translationalresearch_role_voter'] = new \Oleg\TranslationalResearchBundle\Security\Voter\TranslationalResearchRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'user_permission_voter' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Security\Voter\UserPermissionVoter
     */
    protected function getUserPermissionVoterService()
    {
        return $this->services['user_permission_voter'] = new \Oleg\UserdirectoryBundle\Security\Voter\UserPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'user_role_voter' shared service.
     *
     * @return \Oleg\UserdirectoryBundle\Security\Voter\UserRoleVoter
     */
    protected function getUserRoleVoterService()
    {
        return $this->services['user_role_voter'] = new \Oleg\UserdirectoryBundle\Security\Voter\UserRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'vacreq_permission_voter' shared service.
     *
     * @return \Oleg\VacReqBundle\Security\Voter\VacReqPermissionVoter
     */
    protected function getVacreqPermissionVoterService()
    {
        return $this->services['vacreq_permission_voter'] = new \Oleg\VacReqBundle\Security\Voter\VacReqPermissionVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'vacreq_role_voter' shared service.
     *
     * @return \Oleg\VacReqBundle\Security\Voter\VacReqRoleVoter
     */
    protected function getVacreqRoleVoterService()
    {
        return $this->services['vacreq_role_voter'] = new \Oleg\VacReqBundle\Security\Voter\VacReqRoleVoter(${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->get('doctrine.orm.default_entity_manager')) && false ?: '_'}, $this);
    }

    /**
     * Gets the private 'web_profiler.csp.handler' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Csp\ContentSecurityPolicyHandler
     */
    protected function getWebProfiler_Csp_HandlerService()
    {
        return $this->services['web_profiler.csp.handler'] = new \Symfony\Bundle\WebProfilerBundle\Csp\ContentSecurityPolicyHandler(new \Symfony\Bundle\WebProfilerBundle\Csp\NonceGenerator());
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters) || isset($this->loadedDynamicParameters[$name]))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters) || isset($this->loadedDynamicParameters[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }

        return $this->parameterBag;
    }

    private $loadedDynamicParameters = array(
        'kernel.root_dir' => false,
        'kernel.project_dir' => false,
        'kernel.logs_dir' => false,
        'kernel.bundles_metadata' => false,
        'swiftmailer.spool.default.file.path' => false,
        'assetic.read_from' => false,
        'assetic.write_to' => false,
    );
    private $dynamicParameters = array();

    /**
     * Computes a dynamic parameter.
     *
     * @param string The name of the dynamic parameter to load
     *
     * @return mixed The value of the dynamic parameter
     *
     * @throws InvalidArgumentException When the dynamic parameter does not exist
     */
    private function getDynamicParameter($name)
    {
        switch ($name) {
            case 'kernel.root_dir': $value = ($this->targetDirs[3].'\\app'); break;
            case 'kernel.project_dir': $value = $this->targetDirs[3]; break;
            case 'kernel.logs_dir': $value = ($this->targetDirs[2].'\\logs'); break;
            case 'kernel.bundles_metadata': $value = array(
                'FrameworkBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\FrameworkBundle'),
                    'namespace' => 'Symfony\\Bundle\\FrameworkBundle',
                ),
                'SecurityBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\SecurityBundle'),
                    'namespace' => 'Symfony\\Bundle\\SecurityBundle',
                ),
                'TwigBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\TwigBundle'),
                    'namespace' => 'Symfony\\Bundle\\TwigBundle',
                ),
                'MonologBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\monolog-bundle'),
                    'namespace' => 'Symfony\\Bundle\\MonologBundle',
                ),
                'SwiftmailerBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\swiftmailer-bundle'),
                    'namespace' => 'Symfony\\Bundle\\SwiftmailerBundle',
                ),
                'AsseticBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\assetic-bundle'),
                    'namespace' => 'Symfony\\Bundle\\AsseticBundle',
                ),
                'DoctrineBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\doctrine\\doctrine-bundle'),
                    'namespace' => 'Doctrine\\Bundle\\DoctrineBundle',
                ),
                'SensioFrameworkExtraBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\sensio\\framework-extra-bundle'),
                    'namespace' => 'Sensio\\Bundle\\FrameworkExtraBundle',
                ),
                'KnpPaginatorBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\knplabs\\knp-paginator-bundle'),
                    'namespace' => 'Knp\\Bundle\\PaginatorBundle',
                ),
                'FOSUserBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\friendsofsymfony\\user-bundle'),
                    'namespace' => 'FOS\\UserBundle',
                ),
                'OneupUploaderBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\oneup\\uploader-bundle\\Oneup\\UploaderBundle'),
                    'namespace' => 'Oneup\\UploaderBundle',
                ),
                'FOSJsRoutingBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\friendsofsymfony\\jsrouting-bundle'),
                    'namespace' => 'FOS\\JsRoutingBundle',
                ),
                'StofDoctrineExtensionsBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\stof\\doctrine-extensions-bundle'),
                    'namespace' => 'Stof\\DoctrineExtensionsBundle',
                ),
                'EnseparHtml2pdfBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\ensepar\\html2pdf-bundle\\Ensepar\\Html2pdfBundle'),
                    'namespace' => 'Ensepar\\Html2pdfBundle',
                ),
                'SpraedPDFGeneratorBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\spraed\\pdf-generator-bundle\\Spraed\\PDFGeneratorBundle'),
                    'namespace' => 'Spraed\\PDFGeneratorBundle',
                ),
                'KnpSnappyBundle' => array(
                    'parent' => NULL,
                    'path' => 'C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/scanorder/Scanorders2/vendor/knplabs/knp-snappy-bundle',
                    'namespace' => 'Knp\\Bundle\\SnappyBundle',
                ),
                'ADesignsCalendarBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\adesigns\\calendar-bundle\\ADesigns\\CalendarBundle'),
                    'namespace' => 'ADesigns\\CalendarBundle',
                ),
                'BmatznerFontAwesomeBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\bmatzner\\fontawesome-bundle\\Bmatzner\\FontAwesomeBundle'),
                    'namespace' => 'Bmatzner\\FontAwesomeBundle',
                ),
                'OlegUserdirectoryBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\UserdirectoryBundle'),
                    'namespace' => 'Oleg\\UserdirectoryBundle',
                ),
                'OlegOrderformBundle' => array(
                    'parent' => 'FOSUserBundle',
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\OrderformBundle'),
                    'namespace' => 'Oleg\\OrderformBundle',
                ),
                'OlegFellAppBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\FellAppBundle'),
                    'namespace' => 'Oleg\\FellAppBundle',
                ),
                'OlegDeidentifierBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\DeidentifierBundle'),
                    'namespace' => 'Oleg\\DeidentifierBundle',
                ),
                'OlegVacReqBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\VacReqBundle'),
                    'namespace' => 'Oleg\\VacReqBundle',
                ),
                'OlegCallLogBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\CallLogBundle'),
                    'namespace' => 'Oleg\\CallLogBundle',
                ),
                'OlegTranslationalResearchBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\src\\Oleg\\TranslationalResearchBundle'),
                    'namespace' => 'Oleg\\TranslationalResearchBundle',
                ),
                'DebugBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\DebugBundle'),
                    'namespace' => 'Symfony\\Bundle\\DebugBundle',
                ),
                'WebProfilerBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\WebProfilerBundle'),
                    'namespace' => 'Symfony\\Bundle\\WebProfilerBundle',
                ),
                'SensioDistributionBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\sensio\\distribution-bundle'),
                    'namespace' => 'Sensio\\Bundle\\DistributionBundle',
                ),
                'WebServerBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\WebServerBundle'),
                    'namespace' => 'Symfony\\Bundle\\WebServerBundle',
                ),
                'SensioGeneratorBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[3].'\\vendor\\sensio\\generator-bundle'),
                    'namespace' => 'Sensio\\Bundle\\GeneratorBundle',
                ),
            ); break;
            case 'swiftmailer.spool.default.file.path': $value = ($this->targetDirs[3].'\\app/spool/default'); break;
            case 'assetic.read_from': $value = ($this->targetDirs[3].'\\app/../web'); break;
            case 'assetic.write_to': $value = ($this->targetDirs[3].'\\app/../web'); break;
            default: throw new InvalidArgumentException(sprintf('The dynamic parameter "%s" must be defined.', $name));
        }
        $this->loadedDynamicParameters[$name] = true;

        return $this->dynamicParameters[$name] = $value;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'kernel.environment' => 'dev',
            'kernel.debug' => true,
            'kernel.name' => 'app',
            'kernel.cache_dir' => __DIR__,
            'kernel.bundles' => array(
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'SecurityBundle' => 'Symfony\\Bundle\\SecurityBundle\\SecurityBundle',
                'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
                'MonologBundle' => 'Symfony\\Bundle\\MonologBundle\\MonologBundle',
                'SwiftmailerBundle' => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle',
                'AsseticBundle' => 'Symfony\\Bundle\\AsseticBundle\\AsseticBundle',
                'DoctrineBundle' => 'Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle',
                'SensioFrameworkExtraBundle' => 'Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle',
                'KnpPaginatorBundle' => 'Knp\\Bundle\\PaginatorBundle\\KnpPaginatorBundle',
                'FOSUserBundle' => 'FOS\\UserBundle\\FOSUserBundle',
                'OneupUploaderBundle' => 'Oneup\\UploaderBundle\\OneupUploaderBundle',
                'FOSJsRoutingBundle' => 'FOS\\JsRoutingBundle\\FOSJsRoutingBundle',
                'StofDoctrineExtensionsBundle' => 'Stof\\DoctrineExtensionsBundle\\StofDoctrineExtensionsBundle',
                'EnseparHtml2pdfBundle' => 'Ensepar\\Html2pdfBundle\\EnseparHtml2pdfBundle',
                'SpraedPDFGeneratorBundle' => 'Spraed\\PDFGeneratorBundle\\SpraedPDFGeneratorBundle',
                'KnpSnappyBundle' => 'Knp\\Bundle\\SnappyBundle\\KnpSnappyBundle',
                'ADesignsCalendarBundle' => 'ADesigns\\CalendarBundle\\ADesignsCalendarBundle',
                'BmatznerFontAwesomeBundle' => 'Bmatzner\\FontAwesomeBundle\\BmatznerFontAwesomeBundle',
                'OlegUserdirectoryBundle' => 'Oleg\\UserdirectoryBundle\\OlegUserdirectoryBundle',
                'OlegOrderformBundle' => 'Oleg\\OrderformBundle\\OlegOrderformBundle',
                'OlegFellAppBundle' => 'Oleg\\FellAppBundle\\OlegFellAppBundle',
                'OlegDeidentifierBundle' => 'Oleg\\DeidentifierBundle\\OlegDeidentifierBundle',
                'OlegVacReqBundle' => 'Oleg\\VacReqBundle\\OlegVacReqBundle',
                'OlegCallLogBundle' => 'Oleg\\CallLogBundle\\OlegCallLogBundle',
                'OlegTranslationalResearchBundle' => 'Oleg\\TranslationalResearchBundle\\OlegTranslationalResearchBundle',
                'DebugBundle' => 'Symfony\\Bundle\\DebugBundle\\DebugBundle',
                'WebProfilerBundle' => 'Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle',
                'SensioDistributionBundle' => 'Sensio\\Bundle\\DistributionBundle\\SensioDistributionBundle',
                'WebServerBundle' => 'Symfony\\Bundle\\WebServerBundle\\WebServerBundle',
                'SensioGeneratorBundle' => 'Sensio\\Bundle\\GeneratorBundle\\SensioGeneratorBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'appDevDebugProjectContainer',
            'database_driver' => 'pdo_mysql',
            'database_host' => 'localhost',
            'database_port' => NULL,
            'database_name' => 'ScanOrder',
            'database_user' => 'symfony2',
            'database_password' => 'symfony2',
            'database_driver_aperio' => 'pdo_sqlsrv',
            'database_host_aperio' => 'c.med.cornell.edu',
            'database_port_aperio' => NULL,
            'database_name_aperio' => 'Aperio',
            'database_user_aperio' => 'symfony2_aperio',
            'database_password_aperio' => 'Symfony!2',
            'mailer_transport' => 'smtp',
            'mailer_host' => 'smtp.med.cornell.edu',
            'mailer_user' => NULL,
            'mailer_password' => NULL,
            'locale' => 'en',
            'secret' => '563fd817cf2c4f1f692d90650b6fba50f782ccc9',
            'delivery_strategy' => 'realtime',
            'swift_delivery_addresses' => array(
                0 => 'oli2002@med.cornell.edu',
            ),
            'swift_disable_delivery' => true,
            'employees.sitename' => 'employees',
            'employees.uploadpath' => 'directory/documents',
            'employees.avataruploadpath' => 'directory/avatars',
            'default_time_zone' => 'America/New_York',
            'default_system_email' => 'oli2002@med.cornell.edu',
            'institution_url' => 'http://www.cornell.edu/',
            'institution_name' => 'Cornell University',
            'subinstitution_url' => 'http://weill.cornell.edu',
            'subinstitution_name' => 'Weill Cornell Medicine',
            'department_url' => 'http://www.cornellpathology.com',
            'department_name' => 'Pathology and Laboratory Medicine Department',
            'ldaphost' => 'a.wcmc-ad.net',
            'ldapport' => '389',
            'ldapou' => 'a.wcmc-ad.net',
            'ldapusername' => 'svc_aperio_spectrum',
            'ldappassword' => 'Aperi0,123',
            'ldapexepath' => '../src/Oleg/UserdirectoryBundle/Util/',
            'ldapexefilename' => 'LdapSaslCustom.exe',
            'mainhome_title' => 'Welcome to the O R D E R platform!',
            'listmanager_title' => 'List Manager',
            'eventlog_title' => 'Event Log',
            'sitesettings_title' => 'Site Settings',
            'contentabout_page' => '<p>
                    This site is built on the platform titled "O R D E R" (as in the opposite of disorder).
                </p>

                <p>
                    Designers: Victor Brodsky, Oleg Ivanov
                </p>

                <p>
                    Developer: Oleg Ivanov
                </p>

                <p>
                    Quality Assurance Testers: Oleg Ivanov, Steven Bowe, Emilio Madrigal
                </p>

                <p>
                    We are continuing to improve this software. If you have a suggestion or believe you have encountered an issue, please don\'t hesitate to email
                <a href="mailto:slidescan@med.cornell.edu" target="_top">slidescan@med.cornell.edu</a> and attach relevant screenshots.
                </p>

                <br>

                <p>
                O R D E R is made possible by:
                </p>

                <br>

                <p>

                        <ul>


                    <li>
                        <a href="http://php.net">PHP</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://symfony.com">Symfony</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://doctrine-project.org">Doctrine</a>
                    </li>

                    <br>                  
					
					<li>
                        <a href="https://msdn.microsoft.com/en-us/library/aa366156.aspx">MSDN library: ldap_bind_s</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/SwiftmailerBundle">SwiftmailerBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/AsseticBundle">AsseticBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/FriendsOfSymfony/FOSUserBundle">FOSUserBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://phpexcel.codeplex.com/">PHP Excel</a>
                    </li>

                    <br>

                    <li>

                        <a href="https://github.com/1up-lab/OneupUploaderBundle">OneupUploaderBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.dropzonejs.com/">Dropzone JS</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.jstree.com/">jsTree</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpPaginatorBundle">KnpPaginatorBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://twig.sensiolabs.org/doc/advanced.html">Twig</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://getbootstrap.com/">Bootstrap</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/kriskowal/q">JS promises Q</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jquery.com">jQuery</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jqueryui.com/">jQuery UI</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/RobinHerbots/jquery.inputmask">jQuery Inputmask</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://ivaynberg.github.io/select2/">Select2</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.eyecon.ro/bootstrap-datepicker/">Bootstrap Datepicker</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.malot.fr/bootstrap-datetimepicker/demo.php">Bootstrap DateTime Picker</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/twitter/typeahead.js/">Typeahead with Bloodhound</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://fengyuanchen.github.io/cropper/">Image Cropper</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://handsontable.com/">Handsontable</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpSnappyBundle">KnpSnappyBundle with wkhtmltopdf</a>
                    </li>

                     <br>

                    <li>
                        <a href="https://www.libreoffice.org/">LibreOffice</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/myokyawhtun/PDFMerger">PDFMerger</a>
                    </li>

                    <br>                 

                    <li>
                        <a href="https://github.com/bermi/password-generator">Password Generator</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/andreausu/UsuScryptPasswordEncoderBundle">Password Encoder</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/adesigns/calendar-bundle">jQuery FullCalendar bundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://sciactive.com/pnotify/">PNotify JavaScript notifications</a>
                    </li>

                </ul>
                </p>',
            'scan.sitename' => 'scan',
            'scan.uploadpath' => 'scan-order/documents',
            'fellapp.sitename' => 'fellapp',
            'fellapp.uploadpath' => 'fellapp/documents',
            'vacreq.sitename' => 'vacreq',
            'vacreq.uploadpath' => 'directory/vacreq',
            'deidentifier.sitename' => 'deidentifier',
            'calllog.sitename' => 'calllog',
            'calllog.uploadpath' => NULL,
            'translationalresearch.sitename' => 'translationalresearch',
            'translationalresearch.uploadpath' => NULL,
            'fragment.renderer.hinclude.global_template' => NULL,
            'fragment.path' => '/_fragment',
            'kernel.secret' => '563fd817cf2c4f1f692d90650b6fba50f782ccc9',
            'kernel.http_method_override' => true,
            'kernel.trusted_hosts' => array(

            ),
            'kernel.default_locale' => 'en',
            'templating.helper.code.file_link_format' => NULL,
            'debug.file_link_format' => NULL,
            'session.metadata.storage_key' => '_sf2_meta',
            'session.storage.options' => array(
                'cookie_httponly' => true,
                'gc_probability' => 1,
            ),
            'session.save_path' => (__DIR__.'/sessions'),
            'session.metadata.update_threshold' => '0',
            'form.type_extension.csrf.enabled' => true,
            'form.type_extension.csrf.field_name' => '_token',
            'templating.loader.cache.path' => NULL,
            'templating.engines' => array(
                0 => 'twig',
            ),
            'validator.mapping.cache.prefix' => '',
            'validator.mapping.cache.file' => (__DIR__.'/validation.php'),
            'validator.translation_domain' => 'validators',
            'profiler_listener.only_exceptions' => false,
            'profiler_listener.only_master_requests' => false,
            'profiler.storage.dsn' => ('file:'.__DIR__.'/profiler'),
            'debug.error_handler.throw_at' => -1,
            'debug.container.dump' => (__DIR__.'/appDevDebugProjectContainer.xml'),
            'router.options.generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
            'router.options.matcher_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
            'router.options.matcher_base_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
            'router.options.matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
            'router.options.matcher.cache_class' => 'appDevDebugProjectContainerUrlMatcher',
            'router.options.generator.cache_class' => 'appDevDebugProjectContainerUrlGenerator',
            'router.request_context.host' => 'localhost',
            'router.request_context.scheme' => 'http',
            'router.request_context.base_url' => '',
            'router.resource' => (__DIR__.'/assetic/routing.yml'),
            'router.cache_class_prefix' => 'appDevDebugProjectContainer',
            'request_listener.http_port' => 80,
            'request_listener.https_port' => 443,
            'security.authentication.trust_resolver.anonymous_class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken',
            'security.authentication.trust_resolver.rememberme_class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken',
            'security.role_hierarchy.roles' => array(
                'ROLE_SCANORDER_ALL_PATHOLOGY' => array(
                    0 => 'ROLE_SCANORDER_PATHOLOGY_RESIDENT',
                    1 => 'ROLE_SCANORDER_PATHOLOGY_FELLOW',
                    2 => 'ROLE_SCANORDER_PATHOLOGY_FACULTY',
                ),
                'ROLE_SCANORDER_PATHOLOGY_RESIDENT' => array(
                    0 => 'ROLE_SCANORDER_SUBMITTER',
                    1 => 'ROLE_SCANORDER_ORDERING_PROVIDER',
                ),
                'ROLE_SCANORDER_PATHOLOGY_FELLOW' => array(
                    0 => 'ROLE_SCANORDER_SUBMITTER',
                    1 => 'ROLE_SCANORDER_ORDERING_PROVIDER',
                ),
                'ROLE_SCANORDER_PATHOLOGY_FACULTY' => array(
                    0 => 'ROLE_SCANORDER_SUBMITTER',
                    1 => 'ROLE_SCANORDER_ORDERING_PROVIDER',
                ),
                'ROLE_SCANORDER_SUBMITTER' => array(
                    0 => 'ROLE_USER',
                ),
                'ROLE_SCANORDER_ORDERING_PROVIDER' => array(
                    0 => 'ROLE_USER',
                ),
                'ROLE_SCANORDER_PROCESSOR' => array(
                    0 => 'ROLE_USER',
                    1 => 'ROLE_SCANORDER_SUBMITTER',
                    2 => 'ROLE_SCANORDER_ORDERING_PROVIDER',
                    3 => 'ROLE_SCANORDER_ALL_PATHOLOGY',
                    4 => 'ROLE_EDITOR',
                ),
                'ROLE_SCANORDER_ADMIN' => array(
                    0 => 'ROLE_USER',
                    1 => 'ROLE_SCANORDER_PROCESSOR',
                    2 => 'ROLE_SCANORDER_SUBMITTER',
                    3 => 'ROLE_SCANORDER_ORDERING_PROVIDER',
                    4 => 'ROLE_SCANORDER_ALL_PATHOLOGY',
                    5 => 'ROLE_EDITOR',
                ),
                'ROLE_USERDIRECTORY_EDITOR' => array(
                    0 => 'ROLE_EDITOR',
                    1 => 'ROLE_USER',
                    2 => 'ROLE_USERDIRECTORY_OBSERVER',
                ),
                'ROLE_USERDIRECTORY_ADMIN' => array(
                    0 => 'ROLE_USER',
                    1 => 'ROLE_USERDIRECTORY_OBSERVER',
                    2 => 'ROLE_USERDIRECTORY_EDITOR',
                ),
                'ROLE_FELLAPP_ADMIN' => array(
                    0 => 'ROLE_USER',
                    1 => 'ROLE_FELLAPP_OBSERVER',
                    2 => 'ROLE_USERDIRECTORY_EDITOR',
                ),
                'ROLE_FELLAPP_OBSERVER' => array(
                    0 => 'ROLE_FELLAPP_USER',
                ),
                'ROLE_CALLLOG_PATHOLOGY_ATTENDING' => array(
                    0 => 'ROLE_CALLLOG_USER',
                ),
                'ROLE_CALLLOG_PATHOLOGY_RESIDENT' => array(
                    0 => 'ROLE_CALLLOG_USER',
                ),
                'ROLE_CALLLOG_PATHOLOGY_FELLOW' => array(
                    0 => 'ROLE_CALLLOG_USER',
                ),
                'ROLE_CALLLOG_DATA_QUALITY' => array(
                    0 => 'ROLE_CALLLOG_USER',
                ),
                'ROLE_CALLLOG_ADMIN' => array(
                    0 => 'ROLE_CALLLOG_USER',
                    1 => 'ROLE_CALLLOG_PATHOLOGY_ATTENDING',
                    2 => 'ROLE_CALLLOG_PATHOLOGY_RESIDENT',
                    3 => 'ROLE_CALLLOG_PATHOLOGY_FELLOW',
                    4 => 'ROLE_CALLLOG_DATA_QUALITY',
                    5 => 'ROLE_EDITOR',
                ),
                'ROLE_TRANSLATIONALRESEARCH_ADMIN' => array(
                    0 => 'ROLE_TRANSLATIONALRESEARCH_USER',
                    1 => 'ROLE_EDITOR',
                ),
                'ROLE_PLATFORM_DEPUTY_ADMIN' => array(
                    0 => 'ROLE_ALLOWED_TO_SWITCH',
                    1 => 'ROLE_USERDIRECTORY_ADMIN',
                    2 => 'ROLE_SCANORDER_ADMIN',
                    3 => 'ROLE_FELLAPP_ADMIN',
                    4 => 'ROLE_DEIDENTIFICATOR_ADMIN',
                    5 => 'ROLE_VACREQ_ADMIN',
                    6 => 'ROLE_CALLLOG_ADMIN',
                    7 => 'ROLE_TRANSLATIONALRESEARCH_ADMIN',
                ),
                'ROLE_PLATFORM_ADMIN' => array(
                    0 => 'ROLE_PLATFORM_DEPUTY_ADMIN',
                ),
            ),
            'security.access.denied_url' => NULL,
            'security.authentication.manager.erase_credentials' => true,
            'security.authentication.session_strategy.strategy' => 'migrate',
            'security.access.always_authenticate_before_granting' => false,
            'security.authentication.hide_user_not_found' => true,
            'twig.exception_listener.controller' => 'twig.controller.exception:showAction',
            'twig.form.resources' => array(
                0 => 'form_div_layout.html.twig',
            ),
            'monolog.logger.class' => 'Symfony\\Bridge\\Monolog\\Logger',
            'monolog.gelf.publisher.class' => 'Gelf\\MessagePublisher',
            'monolog.gelfphp.publisher.class' => 'Gelf\\Publisher',
            'monolog.handler.stream.class' => 'Monolog\\Handler\\StreamHandler',
            'monolog.handler.console.class' => 'Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler',
            'monolog.handler.group.class' => 'Monolog\\Handler\\GroupHandler',
            'monolog.handler.buffer.class' => 'Monolog\\Handler\\BufferHandler',
            'monolog.handler.deduplication.class' => 'Monolog\\Handler\\DeduplicationHandler',
            'monolog.handler.rotating_file.class' => 'Monolog\\Handler\\RotatingFileHandler',
            'monolog.handler.syslog.class' => 'Monolog\\Handler\\SyslogHandler',
            'monolog.handler.syslogudp.class' => 'Monolog\\Handler\\SyslogUdpHandler',
            'monolog.handler.null.class' => 'Monolog\\Handler\\NullHandler',
            'monolog.handler.test.class' => 'Monolog\\Handler\\TestHandler',
            'monolog.handler.gelf.class' => 'Monolog\\Handler\\GelfHandler',
            'monolog.handler.rollbar.class' => 'Monolog\\Handler\\RollbarHandler',
            'monolog.handler.flowdock.class' => 'Monolog\\Handler\\FlowdockHandler',
            'monolog.handler.browser_console.class' => 'Monolog\\Handler\\BrowserConsoleHandler',
            'monolog.handler.firephp.class' => 'Symfony\\Bridge\\Monolog\\Handler\\FirePHPHandler',
            'monolog.handler.chromephp.class' => 'Symfony\\Bridge\\Monolog\\Handler\\ChromePhpHandler',
            'monolog.handler.debug.class' => 'Symfony\\Bridge\\Monolog\\Handler\\DebugHandler',
            'monolog.handler.swift_mailer.class' => 'Symfony\\Bridge\\Monolog\\Handler\\SwiftMailerHandler',
            'monolog.handler.native_mailer.class' => 'Monolog\\Handler\\NativeMailerHandler',
            'monolog.handler.socket.class' => 'Monolog\\Handler\\SocketHandler',
            'monolog.handler.pushover.class' => 'Monolog\\Handler\\PushoverHandler',
            'monolog.handler.raven.class' => 'Monolog\\Handler\\RavenHandler',
            'monolog.handler.newrelic.class' => 'Monolog\\Handler\\NewRelicHandler',
            'monolog.handler.hipchat.class' => 'Monolog\\Handler\\HipChatHandler',
            'monolog.handler.slack.class' => 'Monolog\\Handler\\SlackHandler',
            'monolog.handler.cube.class' => 'Monolog\\Handler\\CubeHandler',
            'monolog.handler.amqp.class' => 'Monolog\\Handler\\AmqpHandler',
            'monolog.handler.error_log.class' => 'Monolog\\Handler\\ErrorLogHandler',
            'monolog.handler.loggly.class' => 'Monolog\\Handler\\LogglyHandler',
            'monolog.handler.logentries.class' => 'Monolog\\Handler\\LogEntriesHandler',
            'monolog.handler.whatfailuregroup.class' => 'Monolog\\Handler\\WhatFailureGroupHandler',
            'monolog.activation_strategy.not_found.class' => 'Symfony\\Bundle\\MonologBundle\\NotFoundActivationStrategy',
            'monolog.handler.fingers_crossed.class' => 'Monolog\\Handler\\FingersCrossedHandler',
            'monolog.handler.fingers_crossed.error_level_activation_strategy.class' => 'Monolog\\Handler\\FingersCrossed\\ErrorLevelActivationStrategy',
            'monolog.handler.filter.class' => 'Monolog\\Handler\\FilterHandler',
            'monolog.handler.mongo.class' => 'Monolog\\Handler\\MongoDBHandler',
            'monolog.mongo.client.class' => 'MongoClient',
            'monolog.handler.elasticsearch.class' => 'Monolog\\Handler\\ElasticSearchHandler',
            'monolog.elastica.client.class' => 'Elastica\\Client',
            'monolog.use_microseconds' => true,
            'monolog.swift_mailer.handlers' => array(

            ),
            'monolog.handlers_to_channels' => array(
                'monolog.handler.console' => NULL,
                'monolog.handler.syslog' => NULL,
                'monolog.handler.main' => NULL,
            ),
            'swiftmailer.class' => 'Swift_Mailer',
            'swiftmailer.transport.sendmail.class' => 'Swift_Transport_SendmailTransport',
            'swiftmailer.transport.mail.class' => 'Swift_Transport_MailTransport',
            'swiftmailer.transport.failover.class' => 'Swift_Transport_FailoverTransport',
            'swiftmailer.plugin.redirecting.class' => 'Swift_Plugins_RedirectingPlugin',
            'swiftmailer.plugin.impersonate.class' => 'Swift_Plugins_ImpersonatePlugin',
            'swiftmailer.plugin.messagelogger.class' => 'Swift_Plugins_MessageLogger',
            'swiftmailer.plugin.antiflood.class' => 'Swift_Plugins_AntiFloodPlugin',
            'swiftmailer.transport.smtp.class' => 'Swift_Transport_EsmtpTransport',
            'swiftmailer.plugin.blackhole.class' => 'Swift_Plugins_BlackholePlugin',
            'swiftmailer.spool.file.class' => 'Swift_FileSpool',
            'swiftmailer.spool.memory.class' => 'Swift_MemorySpool',
            'swiftmailer.email_sender.listener.class' => 'Symfony\\Bundle\\SwiftmailerBundle\\EventListener\\EmailSenderListener',
            'swiftmailer.data_collector.class' => 'Symfony\\Bundle\\SwiftmailerBundle\\DataCollector\\MessageDataCollector',
            'swiftmailer.mailer.default.transport.name' => 'smtp',
            'swiftmailer.mailer.default.transport.smtp.encryption' => NULL,
            'swiftmailer.mailer.default.transport.smtp.port' => 25,
            'swiftmailer.mailer.default.transport.smtp.host' => 'smtp.med.cornell.edu',
            'swiftmailer.mailer.default.transport.smtp.username' => NULL,
            'swiftmailer.mailer.default.transport.smtp.password' => NULL,
            'swiftmailer.mailer.default.transport.smtp.auth_mode' => NULL,
            'swiftmailer.mailer.default.transport.smtp.timeout' => 30,
            'swiftmailer.mailer.default.transport.smtp.source_ip' => NULL,
            'swiftmailer.mailer.default.transport.smtp.local_domain' => NULL,
            'swiftmailer.mailer.default.spool.enabled' => true,
            'swiftmailer.mailer.default.plugin.impersonate' => NULL,
            'swiftmailer.mailer.default.single_address' => 'oli2002@med.cornell.edu',
            'swiftmailer.mailer.default.delivery_addresses' => array(
                0 => 'oli2002@med.cornell.edu',
            ),
            'swiftmailer.mailer.default.delivery_whitelist' => array(

            ),
            'swiftmailer.mailer.default.delivery.enabled' => true,
            'swiftmailer.spool.enabled' => true,
            'swiftmailer.delivery.enabled' => true,
            'swiftmailer.single_address' => 'oli2002@med.cornell.edu',
            'swiftmailer.mailers' => array(
                'default' => 'swiftmailer.mailer.default',
            ),
            'swiftmailer.default_mailer' => 'default',
            'assetic.asset_factory.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\AssetFactory',
            'assetic.asset_manager.class' => 'Assetic\\Factory\\LazyAssetManager',
            'assetic.asset_manager_cache_warmer.class' => 'Symfony\\Bundle\\AsseticBundle\\CacheWarmer\\AssetManagerCacheWarmer',
            'assetic.cached_formula_loader.class' => 'Assetic\\Factory\\Loader\\CachedFormulaLoader',
            'assetic.config_cache.class' => 'Assetic\\Cache\\ConfigCache',
            'assetic.config_loader.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Loader\\ConfigurationLoader',
            'assetic.config_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\ConfigurationResource',
            'assetic.coalescing_directory_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\CoalescingDirectoryResource',
            'assetic.directory_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\DirectoryResource',
            'assetic.filter_manager.class' => 'Symfony\\Bundle\\AsseticBundle\\FilterManager',
            'assetic.worker.ensure_filter.class' => 'Assetic\\Factory\\Worker\\EnsureFilterWorker',
            'assetic.worker.cache_busting.class' => 'Assetic\\Factory\\Worker\\CacheBustingWorker',
            'assetic.value_supplier.class' => 'Symfony\\Bundle\\AsseticBundle\\DefaultValueSupplier',
            'assetic.node.paths' => array(

            ),
            'assetic.cache_dir' => (__DIR__.'/assetic'),
            'assetic.bundles' => array(
                0 => 'OlegUserdirectoryBundle',
                1 => 'OlegOrderformBundle',
                2 => 'OlegFellAppBundle',
                3 => 'OlegDeidentifierBundle',
                4 => 'OlegVacReqBundle',
                5 => 'OlegCallLogBundle',
                6 => 'OlegTranslationalResearchBundle',
            ),
            'assetic.twig_extension.class' => 'Symfony\\Bundle\\AsseticBundle\\Twig\\AsseticExtension',
            'assetic.twig_formula_loader.class' => 'Assetic\\Extension\\Twig\\TwigFormulaLoader',
            'assetic.helper.dynamic.class' => 'Symfony\\Bundle\\AsseticBundle\\Templating\\DynamicAsseticHelper',
            'assetic.helper.static.class' => 'Symfony\\Bundle\\AsseticBundle\\Templating\\StaticAsseticHelper',
            'assetic.php_formula_loader.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Loader\\AsseticHelperFormulaLoader',
            'assetic.debug' => true,
            'assetic.use_controller' => true,
            'assetic.enable_profiler' => false,
            'assetic.variables' => array(

            ),
            'assetic.java.bin' => 'C:\\ProgramData\\Oracle\\Java\\javapath\\java.EXE',
            'assetic.node.bin' => '/usr/bin/node',
            'assetic.ruby.bin' => '/usr/bin/ruby',
            'assetic.sass.bin' => '/usr/bin/sass',
            'assetic.reactjsx.bin' => '/usr/bin/jsx',
            'assetic.filter.cssrewrite.class' => 'Assetic\\Filter\\CssRewriteFilter',
            'assetic.twig_extension.functions' => array(

            ),
            'assetic.controller.class' => 'Symfony\\Bundle\\AsseticBundle\\Controller\\AsseticController',
            'assetic.routing_loader.class' => 'Symfony\\Bundle\\AsseticBundle\\Routing\\AsseticLoader',
            'assetic.cache.class' => 'Assetic\\Cache\\FilesystemCache',
            'assetic.use_controller_worker.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Worker\\UseControllerWorker',
            'assetic.request_listener.class' => 'Symfony\\Bundle\\AsseticBundle\\EventListener\\RequestListener',
            'doctrine_cache.apc.class' => 'Doctrine\\Common\\Cache\\ApcCache',
            'doctrine_cache.apcu.class' => 'Doctrine\\Common\\Cache\\ApcuCache',
            'doctrine_cache.array.class' => 'Doctrine\\Common\\Cache\\ArrayCache',
            'doctrine_cache.chain.class' => 'Doctrine\\Common\\Cache\\ChainCache',
            'doctrine_cache.couchbase.class' => 'Doctrine\\Common\\Cache\\CouchbaseCache',
            'doctrine_cache.couchbase.connection.class' => 'Couchbase',
            'doctrine_cache.couchbase.hostnames' => 'localhost:8091',
            'doctrine_cache.file_system.class' => 'Doctrine\\Common\\Cache\\FilesystemCache',
            'doctrine_cache.php_file.class' => 'Doctrine\\Common\\Cache\\PhpFileCache',
            'doctrine_cache.memcache.class' => 'Doctrine\\Common\\Cache\\MemcacheCache',
            'doctrine_cache.memcache.connection.class' => 'Memcache',
            'doctrine_cache.memcache.host' => 'localhost',
            'doctrine_cache.memcache.port' => 11211,
            'doctrine_cache.memcached.class' => 'Doctrine\\Common\\Cache\\MemcachedCache',
            'doctrine_cache.memcached.connection.class' => 'Memcached',
            'doctrine_cache.memcached.host' => 'localhost',
            'doctrine_cache.memcached.port' => 11211,
            'doctrine_cache.mongodb.class' => 'Doctrine\\Common\\Cache\\MongoDBCache',
            'doctrine_cache.mongodb.collection.class' => 'MongoCollection',
            'doctrine_cache.mongodb.connection.class' => 'MongoClient',
            'doctrine_cache.mongodb.server' => 'localhost:27017',
            'doctrine_cache.predis.client.class' => 'Predis\\Client',
            'doctrine_cache.predis.scheme' => 'tcp',
            'doctrine_cache.predis.host' => 'localhost',
            'doctrine_cache.predis.port' => 6379,
            'doctrine_cache.redis.class' => 'Doctrine\\Common\\Cache\\RedisCache',
            'doctrine_cache.redis.connection.class' => 'Redis',
            'doctrine_cache.redis.host' => 'localhost',
            'doctrine_cache.redis.port' => 6379,
            'doctrine_cache.riak.class' => 'Doctrine\\Common\\Cache\\RiakCache',
            'doctrine_cache.riak.bucket.class' => 'Riak\\Bucket',
            'doctrine_cache.riak.connection.class' => 'Riak\\Connection',
            'doctrine_cache.riak.bucket_property_list.class' => 'Riak\\BucketPropertyList',
            'doctrine_cache.riak.host' => 'localhost',
            'doctrine_cache.riak.port' => 8087,
            'doctrine_cache.sqlite3.class' => 'Doctrine\\Common\\Cache\\SQLite3Cache',
            'doctrine_cache.sqlite3.connection.class' => 'SQLite3',
            'doctrine_cache.void.class' => 'Doctrine\\Common\\Cache\\VoidCache',
            'doctrine_cache.wincache.class' => 'Doctrine\\Common\\Cache\\WinCacheCache',
            'doctrine_cache.xcache.class' => 'Doctrine\\Common\\Cache\\XcacheCache',
            'doctrine_cache.zenddata.class' => 'Doctrine\\Common\\Cache\\ZendDataCache',
            'doctrine_cache.security.acl.cache.class' => 'Doctrine\\Bundle\\DoctrineCacheBundle\\Acl\\Model\\AclCache',
            'doctrine.dbal.logger.chain.class' => 'Doctrine\\DBAL\\Logging\\LoggerChain',
            'doctrine.dbal.logger.profiling.class' => 'Doctrine\\DBAL\\Logging\\DebugStack',
            'doctrine.dbal.logger.class' => 'Symfony\\Bridge\\Doctrine\\Logger\\DbalLogger',
            'doctrine.dbal.configuration.class' => 'Doctrine\\DBAL\\Configuration',
            'doctrine.data_collector.class' => 'Doctrine\\Bundle\\DoctrineBundle\\DataCollector\\DoctrineDataCollector',
            'doctrine.dbal.connection.event_manager.class' => 'Symfony\\Bridge\\Doctrine\\ContainerAwareEventManager',
            'doctrine.dbal.connection_factory.class' => 'Doctrine\\Bundle\\DoctrineBundle\\ConnectionFactory',
            'doctrine.dbal.events.mysql_session_init.class' => 'Doctrine\\DBAL\\Event\\Listeners\\MysqlSessionInit',
            'doctrine.dbal.events.oracle_session_init.class' => 'Doctrine\\DBAL\\Event\\Listeners\\OracleSessionInit',
            'doctrine.class' => 'Doctrine\\Bundle\\DoctrineBundle\\Registry',
            'doctrine.entity_managers' => array(
                'default' => 'doctrine.orm.default_entity_manager',
                'aperio' => 'doctrine.orm.aperio_entity_manager',
            ),
            'doctrine.default_entity_manager' => 'default',
            'doctrine.dbal.connection_factory.types' => array(

            ),
            'doctrine.connections' => array(
                'default' => 'doctrine.dbal.default_connection',
                'aperio' => 'doctrine.dbal.aperio_connection',
            ),
            'doctrine.default_connection' => 'default',
            'doctrine.orm.configuration.class' => 'Doctrine\\ORM\\Configuration',
            'doctrine.orm.entity_manager.class' => 'Doctrine\\ORM\\EntityManager',
            'doctrine.orm.manager_configurator.class' => 'Doctrine\\Bundle\\DoctrineBundle\\ManagerConfigurator',
            'doctrine.orm.cache.array.class' => 'Doctrine\\Common\\Cache\\ArrayCache',
            'doctrine.orm.cache.apc.class' => 'Doctrine\\Common\\Cache\\ApcCache',
            'doctrine.orm.cache.memcache.class' => 'Doctrine\\Common\\Cache\\MemcacheCache',
            'doctrine.orm.cache.memcache_host' => 'localhost',
            'doctrine.orm.cache.memcache_port' => 11211,
            'doctrine.orm.cache.memcache_instance.class' => 'Memcache',
            'doctrine.orm.cache.memcached.class' => 'Doctrine\\Common\\Cache\\MemcachedCache',
            'doctrine.orm.cache.memcached_host' => 'localhost',
            'doctrine.orm.cache.memcached_port' => 11211,
            'doctrine.orm.cache.memcached_instance.class' => 'Memcached',
            'doctrine.orm.cache.redis.class' => 'Doctrine\\Common\\Cache\\RedisCache',
            'doctrine.orm.cache.redis_host' => 'localhost',
            'doctrine.orm.cache.redis_port' => 6379,
            'doctrine.orm.cache.redis_instance.class' => 'Redis',
            'doctrine.orm.cache.xcache.class' => 'Doctrine\\Common\\Cache\\XcacheCache',
            'doctrine.orm.cache.wincache.class' => 'Doctrine\\Common\\Cache\\WinCacheCache',
            'doctrine.orm.cache.zenddata.class' => 'Doctrine\\Common\\Cache\\ZendDataCache',
            'doctrine.orm.metadata.driver_chain.class' => 'Doctrine\\Common\\Persistence\\Mapping\\Driver\\MappingDriverChain',
            'doctrine.orm.metadata.annotation.class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
            'doctrine.orm.metadata.xml.class' => 'Doctrine\\ORM\\Mapping\\Driver\\SimplifiedXmlDriver',
            'doctrine.orm.metadata.yml.class' => 'Doctrine\\ORM\\Mapping\\Driver\\SimplifiedYamlDriver',
            'doctrine.orm.metadata.php.class' => 'Doctrine\\ORM\\Mapping\\Driver\\PHPDriver',
            'doctrine.orm.metadata.staticphp.class' => 'Doctrine\\ORM\\Mapping\\Driver\\StaticPHPDriver',
            'doctrine.orm.proxy_cache_warmer.class' => 'Symfony\\Bridge\\Doctrine\\CacheWarmer\\ProxyCacheWarmer',
            'form.type_guesser.doctrine.class' => 'Symfony\\Bridge\\Doctrine\\Form\\DoctrineOrmTypeGuesser',
            'doctrine.orm.validator.unique.class' => 'Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator',
            'doctrine.orm.validator_initializer.class' => 'Symfony\\Bridge\\Doctrine\\Validator\\DoctrineInitializer',
            'doctrine.orm.security.user.provider.class' => 'Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider',
            'doctrine.orm.listeners.resolve_target_entity.class' => 'Doctrine\\ORM\\Tools\\ResolveTargetEntityListener',
            'doctrine.orm.listeners.attach_entity_listeners.class' => 'Doctrine\\ORM\\Tools\\AttachEntityListenersListener',
            'doctrine.orm.naming_strategy.default.class' => 'Doctrine\\ORM\\Mapping\\DefaultNamingStrategy',
            'doctrine.orm.naming_strategy.underscore.class' => 'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy',
            'doctrine.orm.quote_strategy.default.class' => 'Doctrine\\ORM\\Mapping\\DefaultQuoteStrategy',
            'doctrine.orm.quote_strategy.ansi.class' => 'Doctrine\\ORM\\Mapping\\AnsiQuoteStrategy',
            'doctrine.orm.entity_listener_resolver.class' => 'Doctrine\\Bundle\\DoctrineBundle\\Mapping\\ContainerAwareEntityListenerResolver',
            'doctrine.orm.second_level_cache.default_cache_factory.class' => 'Doctrine\\ORM\\Cache\\DefaultCacheFactory',
            'doctrine.orm.second_level_cache.default_region.class' => 'Doctrine\\ORM\\Cache\\Region\\DefaultRegion',
            'doctrine.orm.second_level_cache.filelock_region.class' => 'Doctrine\\ORM\\Cache\\Region\\FileLockRegion',
            'doctrine.orm.second_level_cache.logger_chain.class' => 'Doctrine\\ORM\\Cache\\Logging\\CacheLoggerChain',
            'doctrine.orm.second_level_cache.logger_statistics.class' => 'Doctrine\\ORM\\Cache\\Logging\\StatisticsCacheLogger',
            'doctrine.orm.second_level_cache.cache_configuration.class' => 'Doctrine\\ORM\\Cache\\CacheConfiguration',
            'doctrine.orm.second_level_cache.regions_configuration.class' => 'Doctrine\\ORM\\Cache\\RegionsConfiguration',
            'doctrine.orm.auto_generate_proxy_classes' => true,
            'doctrine.orm.proxy_dir' => (__DIR__.'/doctrine/orm/Proxies'),
            'doctrine.orm.proxy_namespace' => 'Proxies',
            'sensio_framework_extra.view.guesser.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Templating\\TemplateGuesser',
            'sensio_framework_extra.controller.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener',
            'sensio_framework_extra.routing.loader.annot_dir.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader',
            'sensio_framework_extra.routing.loader.annot_file.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader',
            'sensio_framework_extra.routing.loader.annot_class.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Routing\\AnnotatedRouteControllerLoader',
            'sensio_framework_extra.converter.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ParamConverterListener',
            'sensio_framework_extra.converter.manager.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterManager',
            'sensio_framework_extra.converter.doctrine.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter',
            'sensio_framework_extra.converter.datetime.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DateTimeParamConverter',
            'sensio_framework_extra.view.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener',
            'knp_paginator.class' => 'Knp\\Component\\Pager\\Paginator',
            'knp_paginator.helper.processor.class' => 'Knp\\Bundle\\PaginatorBundle\\Helper\\Processor',
            'knp_paginator.template.pagination' => 'KnpPaginatorBundle:Pagination:sliding.html.twig',
            'knp_paginator.template.filtration' => 'KnpPaginatorBundle:Pagination:filtration.html.twig',
            'knp_paginator.template.sortable' => 'KnpPaginatorBundle:Pagination:sortable_link.html.twig',
            'knp_paginator.page_range' => 5,
            'fos_user.backend_type_orm' => true,
            'fos_user.security.interactive_login_listener.class' => 'FOS\\UserBundle\\EventListener\\LastLoginListener',
            'fos_user.security.login_manager.class' => 'FOS\\UserBundle\\Security\\LoginManager',
            'fos_user.resetting.email.template' => '@FOSUser/Resetting/email.txt.twig',
            'fos_user.registration.confirmation.template' => '@FOSUser/Registration/email.txt.twig',
            'fos_user.storage' => 'orm',
            'fos_user.firewall_name' => 'main',
            'fos_user.model_manager_name' => NULL,
            'fos_user.model.user.class' => 'Oleg\\UserdirectoryBundle\\Entity\\User',
            'fos_user.profile.form.type' => 'FOS\\UserBundle\\Form\\Type\\ProfileFormType',
            'fos_user.profile.form.name' => 'fos_user_profile_form',
            'fos_user.profile.form.validation_groups' => array(
                0 => 'Profile',
                1 => 'Default',
            ),
            'fos_user.registration.confirmation.from_email' => array(
                'oli2002@med.cornell.edu' => 'Oleg Ivanov',
            ),
            'fos_user.registration.confirmation.enabled' => false,
            'fos_user.registration.form.type' => 'FOS\\UserBundle\\Form\\Type\\RegistrationFormType',
            'fos_user.registration.form.name' => 'fos_user_registration_form',
            'fos_user.registration.form.validation_groups' => array(
                0 => 'Registration',
                1 => 'Default',
            ),
            'fos_user.change_password.form.type' => 'FOS\\UserBundle\\Form\\Type\\ChangePasswordFormType',
            'fos_user.change_password.form.name' => 'fos_user_change_password_form',
            'fos_user.change_password.form.validation_groups' => array(
                0 => 'ChangePassword',
                1 => 'Default',
            ),
            'fos_user.resetting.email.from_email' => array(
                'oli2002@med.cornell.edu' => 'Oleg Ivanov',
            ),
            'fos_user.resetting.retry_ttl' => 7200,
            'fos_user.resetting.token_ttl' => 86400,
            'fos_user.resetting.form.type' => 'FOS\\UserBundle\\Form\\Type\\ResettingFormType',
            'fos_user.resetting.form.name' => 'fos_user_resetting_form',
            'fos_user.resetting.form.validation_groups' => array(
                0 => 'ResetPassword',
                1 => 'Default',
            ),
            'oneup_uploader.chunks.manager.class' => 'Oneup\\UploaderBundle\\Uploader\\Chunk\\ChunkManager',
            'oneup_uploader.chunks_storage.gaufrette.class' => 'Oneup\\UploaderBundle\\Uploader\\Chunk\\Storage\\GaufretteStorage',
            'oneup_uploader.chunks_storage.flysystem.class' => 'Oneup\\UploaderBundle\\Uploader\\Chunk\\Storage\\FlysystemStorage',
            'oneup_uploader.chunks_storage.filesystem.class' => 'Oneup\\UploaderBundle\\Uploader\\Chunk\\Storage\\FilesystemStorage',
            'oneup_uploader.namer.uniqid.class' => 'Oneup\\UploaderBundle\\Uploader\\Naming\\UniqidNamer',
            'oneup_uploader.routing.loader.class' => 'Oneup\\UploaderBundle\\Routing\\RouteLoader',
            'oneup_uploader.storage.gaufrette.class' => 'Oneup\\UploaderBundle\\Uploader\\Storage\\GaufretteStorage',
            'oneup_uploader.storage.flysystem.class' => 'Oneup\\UploaderBundle\\Uploader\\Storage\\FlysystemStorage',
            'oneup_uploader.storage.filesystem.class' => 'Oneup\\UploaderBundle\\Uploader\\Storage\\FilesystemStorage',
            'oneup_uploader.orphanage.class' => 'Oneup\\UploaderBundle\\Uploader\\Storage\\FilesystemOrphanageStorage',
            'oneup_uploader.orphanage.manager.class' => 'Oneup\\UploaderBundle\\Uploader\\Orphanage\\OrphanageManager',
            'oneup_uploader.controller.fineuploader.class' => 'Oneup\\UploaderBundle\\Controller\\FineUploaderController',
            'oneup_uploader.controller.blueimp.class' => 'Oneup\\UploaderBundle\\Controller\\BlueimpController',
            'oneup_uploader.controller.uploadify.class' => 'Oneup\\UploaderBundle\\Controller\\UploadifyController',
            'oneup_uploader.controller.yui3.class' => 'Oneup\\UploaderBundle\\Controller\\YUI3Controller',
            'oneup_uploader.controller.fancyupload.class' => 'Oneup\\UploaderBundle\\Controller\\FancyUploadController',
            'oneup_uploader.controller.mooupload.class' => 'Oneup\\UploaderBundle\\Controller\\MooUploadController',
            'oneup_uploader.controller.plupload.class' => 'Oneup\\UploaderBundle\\Controller\\PluploadController',
            'oneup_uploader.controller.dropzone.class' => 'Oneup\\UploaderBundle\\Controller\\DropzoneController',
            'oneup_uploader.error_handler.noop.class' => 'Oneup\\UploaderBundle\\Uploader\\ErrorHandler\\NoopErrorHandler',
            'oneup_uploader.error_handler.blueimp.class' => 'Oneup\\UploaderBundle\\Uploader\\ErrorHandler\\BlueimpErrorHandler',
            'oneup_uploader.error_handler.dropzone.class' => 'Oneup\\UploaderBundle\\Uploader\\ErrorHandler\\DropzoneErrorHandler',
            'oneup_uploader.chunks' => array(
                'maxage' => 604800,
                'storage' => array(
                    'type' => 'filesystem',
                    'filesystem' => NULL,
                    'directory' => (__DIR__.'/uploader/chunks'),
                    'stream_wrapper' => NULL,
                    'sync_buffer_size' => '100K',
                    'prefix' => 'chunks',
                ),
                'load_distribution' => true,
            ),
            'oneup_uploader.orphanage' => array(
                'maxage' => 604800,
                'directory' => (__DIR__.'/uploader/orphanage'),
            ),
            'oneup_uploader.config.employees_gallery' => array(
                'frontend' => 'dropzone',
                'storage' => array(
                    'directory' => 'Uploaded/directory/documents',
                    'service' => NULL,
                    'type' => 'filesystem',
                    'filesystem' => NULL,
                    'stream_wrapper' => NULL,
                    'sync_buffer_size' => '100K',
                ),
                'custom_frontend' => array(
                    'name' => NULL,
                    'class' => NULL,
                ),
                'route_prefix' => '',
                'allowed_mimetypes' => array(

                ),
                'disallowed_mimetypes' => array(

                ),
                'error_handler' => NULL,
                'max_size' => 2147483647,
                'use_orphanage' => false,
                'enable_progress' => false,
                'enable_cancelation' => false,
                'namer' => 'oneup_uploader.namer.uniqid',
                'root_folder' => false,
            ),
            'oneup_uploader.config.scan_gallery' => array(
                'frontend' => 'dropzone',
                'storage' => array(
                    'directory' => 'Uploaded/scan-order/documents',
                    'service' => NULL,
                    'type' => 'filesystem',
                    'filesystem' => NULL,
                    'stream_wrapper' => NULL,
                    'sync_buffer_size' => '100K',
                ),
                'custom_frontend' => array(
                    'name' => NULL,
                    'class' => NULL,
                ),
                'route_prefix' => '',
                'allowed_mimetypes' => array(

                ),
                'disallowed_mimetypes' => array(

                ),
                'error_handler' => NULL,
                'max_size' => 2147483647,
                'use_orphanage' => false,
                'enable_progress' => false,
                'enable_cancelation' => false,
                'namer' => 'oneup_uploader.namer.uniqid',
                'root_folder' => false,
            ),
            'oneup_uploader.config.fellapp_gallery' => array(
                'frontend' => 'dropzone',
                'storage' => array(
                    'directory' => 'Uploaded/fellapp/documents',
                    'service' => NULL,
                    'type' => 'filesystem',
                    'filesystem' => NULL,
                    'stream_wrapper' => NULL,
                    'sync_buffer_size' => '100K',
                ),
                'custom_frontend' => array(
                    'name' => NULL,
                    'class' => NULL,
                ),
                'route_prefix' => '',
                'allowed_mimetypes' => array(

                ),
                'disallowed_mimetypes' => array(

                ),
                'error_handler' => NULL,
                'max_size' => 2147483647,
                'use_orphanage' => false,
                'enable_progress' => false,
                'enable_cancelation' => false,
                'namer' => 'oneup_uploader.namer.uniqid',
                'root_folder' => false,
            ),
            'oneup_uploader.config.vacreq_gallery' => array(
                'frontend' => 'dropzone',
                'storage' => array(
                    'directory' => 'Uploaded/directory/vacreq',
                    'service' => NULL,
                    'type' => 'filesystem',
                    'filesystem' => NULL,
                    'stream_wrapper' => NULL,
                    'sync_buffer_size' => '100K',
                ),
                'custom_frontend' => array(
                    'name' => NULL,
                    'class' => NULL,
                ),
                'route_prefix' => '',
                'allowed_mimetypes' => array(

                ),
                'disallowed_mimetypes' => array(

                ),
                'error_handler' => NULL,
                'max_size' => 2147483647,
                'use_orphanage' => false,
                'enable_progress' => false,
                'enable_cancelation' => false,
                'namer' => 'oneup_uploader.namer.uniqid',
                'root_folder' => false,
            ),
            'oneup_uploader.config' => array(
                'mappings' => array(
                    'employees_gallery' => array(
                        'frontend' => 'dropzone',
                        'storage' => array(
                            'directory' => 'Uploaded/directory/documents',
                            'service' => NULL,
                            'type' => 'filesystem',
                            'filesystem' => NULL,
                            'stream_wrapper' => NULL,
                            'sync_buffer_size' => '100K',
                        ),
                        'custom_frontend' => array(
                            'name' => NULL,
                            'class' => NULL,
                        ),
                        'route_prefix' => '',
                        'allowed_mimetypes' => array(

                        ),
                        'disallowed_mimetypes' => array(

                        ),
                        'error_handler' => NULL,
                        'max_size' => 2147483647,
                        'use_orphanage' => false,
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'namer' => 'oneup_uploader.namer.uniqid',
                        'root_folder' => false,
                    ),
                    'scan_gallery' => array(
                        'frontend' => 'dropzone',
                        'storage' => array(
                            'directory' => 'Uploaded/scan-order/documents',
                            'service' => NULL,
                            'type' => 'filesystem',
                            'filesystem' => NULL,
                            'stream_wrapper' => NULL,
                            'sync_buffer_size' => '100K',
                        ),
                        'custom_frontend' => array(
                            'name' => NULL,
                            'class' => NULL,
                        ),
                        'route_prefix' => '',
                        'allowed_mimetypes' => array(

                        ),
                        'disallowed_mimetypes' => array(

                        ),
                        'error_handler' => NULL,
                        'max_size' => 2147483647,
                        'use_orphanage' => false,
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'namer' => 'oneup_uploader.namer.uniqid',
                        'root_folder' => false,
                    ),
                    'fellapp_gallery' => array(
                        'frontend' => 'dropzone',
                        'storage' => array(
                            'directory' => 'Uploaded/fellapp/documents',
                            'service' => NULL,
                            'type' => 'filesystem',
                            'filesystem' => NULL,
                            'stream_wrapper' => NULL,
                            'sync_buffer_size' => '100K',
                        ),
                        'custom_frontend' => array(
                            'name' => NULL,
                            'class' => NULL,
                        ),
                        'route_prefix' => '',
                        'allowed_mimetypes' => array(

                        ),
                        'disallowed_mimetypes' => array(

                        ),
                        'error_handler' => NULL,
                        'max_size' => 2147483647,
                        'use_orphanage' => false,
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'namer' => 'oneup_uploader.namer.uniqid',
                        'root_folder' => false,
                    ),
                    'vacreq_gallery' => array(
                        'frontend' => 'dropzone',
                        'storage' => array(
                            'directory' => 'Uploaded/directory/vacreq',
                            'service' => NULL,
                            'type' => 'filesystem',
                            'filesystem' => NULL,
                            'stream_wrapper' => NULL,
                            'sync_buffer_size' => '100K',
                        ),
                        'custom_frontend' => array(
                            'name' => NULL,
                            'class' => NULL,
                        ),
                        'route_prefix' => '',
                        'allowed_mimetypes' => array(

                        ),
                        'disallowed_mimetypes' => array(

                        ),
                        'error_handler' => NULL,
                        'max_size' => 2147483647,
                        'use_orphanage' => false,
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'namer' => 'oneup_uploader.namer.uniqid',
                        'root_folder' => false,
                    ),
                ),
                'chunks' => array(
                    'maxage' => 604800,
                    'storage' => array(
                        'type' => 'filesystem',
                        'filesystem' => NULL,
                        'directory' => (__DIR__.'/uploader/chunks'),
                        'stream_wrapper' => NULL,
                        'sync_buffer_size' => '100K',
                        'prefix' => 'chunks',
                    ),
                    'load_distribution' => true,
                ),
                'orphanage' => array(
                    'maxage' => 604800,
                    'directory' => (__DIR__.'/uploader/orphanage'),
                ),
                'twig' => true,
            ),
            'oneup_uploader.controllers' => array(
                'employees_gallery' => array(
                    0 => 'oneup_uploader.controller.employees_gallery',
                    1 => array(
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'route_prefix' => '',
                    ),
                ),
                'scan_gallery' => array(
                    0 => 'oneup_uploader.controller.scan_gallery',
                    1 => array(
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'route_prefix' => '',
                    ),
                ),
                'fellapp_gallery' => array(
                    0 => 'oneup_uploader.controller.fellapp_gallery',
                    1 => array(
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'route_prefix' => '',
                    ),
                ),
                'vacreq_gallery' => array(
                    0 => 'oneup_uploader.controller.vacreq_gallery',
                    1 => array(
                        'enable_progress' => false,
                        'enable_cancelation' => false,
                        'route_prefix' => '',
                    ),
                ),
            ),
            'oneup_uploader.maxsize' => array(
                'employees_gallery' => 2097152,
                'scan_gallery' => 2097152,
                'fellapp_gallery' => 2097152,
                'vacreq_gallery' => 2097152,
            ),
            'fos_js_routing.extractor.class' => 'FOS\\JsRoutingBundle\\Extractor\\ExposedRoutesExtractor',
            'fos_js_routing.controller.class' => 'FOS\\JsRoutingBundle\\Controller\\Controller',
            'fos_js_routing.cache_control' => array(
                'enabled' => false,
            ),
            'stof_doctrine_extensions.event_listener.locale.class' => 'Stof\\DoctrineExtensionsBundle\\EventListener\\LocaleListener',
            'stof_doctrine_extensions.event_listener.logger.class' => 'Stof\\DoctrineExtensionsBundle\\EventListener\\LoggerListener',
            'stof_doctrine_extensions.event_listener.blame.class' => 'Stof\\DoctrineExtensionsBundle\\EventListener\\BlameListener',
            'stof_doctrine_extensions.uploadable.manager.class' => 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadableManager',
            'stof_doctrine_extensions.uploadable.mime_type_guesser.class' => 'Stof\\DoctrineExtensionsBundle\\Uploadable\\MimeTypeGuesserAdapter',
            'stof_doctrine_extensions.uploadable.default_file_info.class' => 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo',
            'stof_doctrine_extensions.default_locale' => 'en_US',
            'stof_doctrine_extensions.default_file_path' => NULL,
            'stof_doctrine_extensions.translation_fallback' => false,
            'stof_doctrine_extensions.persist_default_translation' => false,
            'stof_doctrine_extensions.skip_translation_on_load' => false,
            'stof_doctrine_extensions.uploadable.validate_writable_directory' => true,
            'stof_doctrine_extensions.listener.translatable.class' => 'Gedmo\\Translatable\\TranslatableListener',
            'stof_doctrine_extensions.listener.timestampable.class' => 'Gedmo\\Timestampable\\TimestampableListener',
            'stof_doctrine_extensions.listener.blameable.class' => 'Gedmo\\Blameable\\BlameableListener',
            'stof_doctrine_extensions.listener.sluggable.class' => 'Gedmo\\Sluggable\\SluggableListener',
            'stof_doctrine_extensions.listener.tree.class' => 'Gedmo\\Tree\\TreeListener',
            'stof_doctrine_extensions.listener.loggable.class' => 'Gedmo\\Loggable\\LoggableListener',
            'stof_doctrine_extensions.listener.sortable.class' => 'Gedmo\\Sortable\\SortableListener',
            'stof_doctrine_extensions.listener.softdeleteable.class' => 'Gedmo\\SoftDeleteable\\SoftDeleteableListener',
            'stof_doctrine_extensions.listener.uploadable.class' => 'Gedmo\\Uploadable\\UploadableListener',
            'stof_doctrine_extensions.listener.reference_integrity.class' => 'Gedmo\\ReferenceIntegrity\\ReferenceIntegrityListener',
            'html2pdf.orientation' => 'P',
            'html2pdf.format' => 'A4',
            'html2pdf.lang' => 'en',
            'html2pdf.unicode' => true,
            'html2pdf.encoding' => 'UTF-8',
            'html2pdf.margin' => array(
                0 => 10,
                1 => 15,
                2 => 10,
                3 => 15,
            ),
            'knp_snappy.pdf.internal_generator.class' => 'Knp\\Snappy\\Pdf',
            'knp_snappy.pdf.class' => 'Knp\\Bundle\\SnappyBundle\\Snappy\\LoggableGenerator',
            'knp_snappy.pdf.binary' => '"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"',
            'knp_snappy.pdf.options' => array(
                'javascript-delay' => 7000,
            ),
            'knp_snappy.pdf.env' => array(

            ),
            'knp_snappy.image.internal_generator.class' => 'Knp\\Snappy\\Image',
            'knp_snappy.image.class' => 'Knp\\Bundle\\SnappyBundle\\Snappy\\LoggableGenerator',
            'knp_snappy.image.binary' => 'wkhtmltoimage',
            'knp_snappy.image.options' => array(

            ),
            'knp_snappy.image.env' => array(

            ),
            'fullcalendar.event.class' => 'ADesigns\\CalendarBundle\\Entity\\EventEntity',
            'fullcalendar.loader.event' => 'calendar.load_events',
            'web_profiler.debug_toolbar.position' => 'bottom',
            'web_profiler.debug_toolbar.intercept_redirects' => false,
            'web_profiler.debug_toolbar.mode' => 2,
            'data_collector.templates' => array(
                'data_collector.request' => array(
                    0 => 'request',
                    1 => '@WebProfiler/Collector/request.html.twig',
                ),
                'data_collector.time' => array(
                    0 => 'time',
                    1 => '@WebProfiler/Collector/time.html.twig',
                ),
                'data_collector.memory' => array(
                    0 => 'memory',
                    1 => '@WebProfiler/Collector/memory.html.twig',
                ),
                'data_collector.ajax' => array(
                    0 => 'ajax',
                    1 => '@WebProfiler/Collector/ajax.html.twig',
                ),
                'data_collector.form' => array(
                    0 => 'form',
                    1 => '@WebProfiler/Collector/form.html.twig',
                ),
                'data_collector.exception' => array(
                    0 => 'exception',
                    1 => '@WebProfiler/Collector/exception.html.twig',
                ),
                'data_collector.logger' => array(
                    0 => 'logger',
                    1 => '@WebProfiler/Collector/logger.html.twig',
                ),
                'data_collector.events' => array(
                    0 => 'events',
                    1 => '@WebProfiler/Collector/events.html.twig',
                ),
                'data_collector.router' => array(
                    0 => 'router',
                    1 => '@WebProfiler/Collector/router.html.twig',
                ),
                'data_collector.cache' => array(
                    0 => 'cache',
                    1 => '@WebProfiler/Collector/cache.html.twig',
                ),
                'data_collector.security' => array(
                    0 => 'security',
                    1 => '@Security/Collector/security.html.twig',
                ),
                'data_collector.twig' => array(
                    0 => 'twig',
                    1 => '@WebProfiler/Collector/twig.html.twig',
                ),
                'data_collector.doctrine' => array(
                    0 => 'db',
                    1 => '@Doctrine/Collector/db.html.twig',
                ),
                'swiftmailer.data_collector' => array(
                    0 => 'swiftmailer',
                    1 => '@Swiftmailer/Collector/swiftmailer.html.twig',
                ),
                'data_collector.dump' => array(
                    0 => 'dump',
                    1 => '@Debug/Profiler/dump.html.twig',
                ),
                'data_collector.config' => array(
                    0 => 'config',
                    1 => '@WebProfiler/Collector/config.html.twig',
                ),
            ),
            'console.command.ids' => array(
                'console.command.symfony_bundle_securitybundle_command_userpasswordencodercommand' => 'console.command.symfony_bundle_securitybundle_command_userpasswordencodercommand',
                'console.command.sensiolabs_security_command_securitycheckercommand' => 'sensio_distribution.security_checker.command',
                'console.command.symfony_bundle_webserverbundle_command_serverruncommand' => 'console.command.symfony_bundle_webserverbundle_command_serverruncommand',
                'console.command.symfony_bundle_webserverbundle_command_serverstartcommand' => 'console.command.symfony_bundle_webserverbundle_command_serverstartcommand',
                'console.command.symfony_bundle_webserverbundle_command_serverstopcommand' => 'console.command.symfony_bundle_webserverbundle_command_serverstopcommand',
                'console.command.symfony_bundle_webserverbundle_command_serverstatuscommand' => 'console.command.symfony_bundle_webserverbundle_command_serverstatuscommand',
            ),
        );
    }
}
