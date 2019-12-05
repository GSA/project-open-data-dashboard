<?php
//note to self, if due date not set, look to parent
$dgs_items = array(

	'generated' => date( 'Y-m-d H:i:s' ),
	'api_version' => 1,
	'items' => array(

		//2.1
		new DGS_Action_Item( array(
				'id' => '2.1',
				'text' => 'Engage with customers to identify at least two existing major customer-facing services that contain high-value data or content as first-move candidates to make compliant with new open data, content, and web API policy.',
				'due' => '90 Days',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						))
				),
			)),

		new DGS_Action_Item( array(
				'id' => '2.1.1',
				'parent' => '2.1',
				'text' => 'Paragraph on customer engagement approach',
				'due' => '90 days',
				'fields' => array(
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'customer-engagement-approach',
							'label' => 'Paragraph on customer engagement approach',
						))
				)
			)),

		new DGS_Action_Item( array(
				'id' => '2.1.2',
				'parent' => '2.1',
				'text' => 'Prioritized list of systems (datasets)',
				'due' => '90 days',
				'multiple' => true,
				'fields' => array(
					new DGS_field( array(
							'type' => 'text',
							'name' => 'name',
							'label' => 'System Name',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'description',
							'label' => 'System Description',
						)),
					new DGS_field( array(
							'type' => 'select',
							'name' => 'scope',
							'label' => 'System Scope',
							'options' => array(
								new DGS_Option( 'internal', 'Internal' ),
								new DGS_Option( 'external', 'External' ),
								new DGS_Option( 'both', 'Both' ),
							),
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'customer',
							'label' => 'Main Customer',
						)),
					new DGS_field( array( 
							'type' => 'text',
							'name' => 'uii',
							'label' => 'Unique Investment Identifier',
						)),
				)
			)),

		//7.1
		new DGS_Action_Item( array(
				'id' => '7.1',
				'text' => 'Engage with customers to identify at least two existing priority customer-facing services to optimize for mobile use.',
				'due' => '90 Days',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						))
				),
			)),

		new DGS_Action_Item( array(
				'id' => '7.1.1',
				'parent' => '7.1',
				'text' => 'Paragraph on customer engagement approach',
				'due' => '90 days',
				'fields' => array(
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'customer-engagement-approach',
							'label' => 'Paragraph on customer engagement approach',
						))
				)
			)),

		new DGS_Action_Item( array(
				'id' => '7.1.2',
				'parent' => '7.1',
				'text' => 'Prioritized list of systems (datasets)',
				'due' => '90 days',
				'multiple' => true,
				'fields' => array(
					new DGS_field( array(
							'type' => 'text',
							'name' => 'name',
							'label' => 'System Name',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'description',
							'label' => 'System Description',
						)),
					new DGS_field( array(
							'type' => 'select',
							'name' => 'scope',
							'label' => 'System Scope',
							'options' => array(
								new DGS_Option( 'internal', 'Internal' ),
								new DGS_Option( 'external', 'External' ),
								new DGS_Option( 'both', 'Both' ),
							),
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'customer',
							'label' => 'Main Customer',
						)),
					new DGS_field( array( 
							'type' => 'text',
							'name' => 'uii',
							'label' => 'Unique Investment Identifier',
						)),
				)
			)),

		//4.2
		new DGS_Action_Item( array(
				'id' => '4.2',
				'text' => 'Establish an agency-wide governance structure for developing and delivering digital services',
				'due' => '6 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
                	new DGS_field( array(
							'type' => 'textarea',
							'name' => 'policy',
							'label' => 'Paragraph on Governance',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'link',
							'label' => 'Link to Governance Document',
						))
				),
			)),

		//5.2
		new DGS_Action_Item( array(
				'id' => '5.2',
				'text' => 'Develop an enterprise-wide inventory of mobile devices and wireless service contracts',
				'due' => '6 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '5.2.1',
				'text' => 'Develop mobile and wireless inventory',
				'due' => '6 months',
				'parent' => '5.2',
				'multiple' => true,
				'fields' => array(
					new DGS_field( array(
							'type' => 'text',
							'name' => 'component',
							'label' => 'Bureau/Component',
						)),
					new DGS_field( array(
							'type' => 'select',
							'name' => 'device-inventory-status',
							'label' => 'Inventory Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
				),
			)),

		//8.2
		new DGS_Action_Item( array(
				'id' => '8.2',
				'text' => 'Implement performance and customer satisfaction measuring tools on all .gov websites',
				'due' => '1-22-13',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '8.2.1',
				'parent' => '8.2',
				'text' => 'Implement performance measurement tool',
				'due' => '1-22-13',
				'fields' => array(
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'implementation',
							'label' => 'Describe Implementation',
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '8.2.2',
				'parent' => '8.2',
				'text' => 'Implement customer satisfaction tool',
				'due' => '1-22-13',
				'fields' => array(
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'implementation',
							'label' => 'Describe Implementation',
						)),
				),
			)),

		//1.2
		new DGS_Action_Item( array(
				'id' => '1.2',
				'text' => 'Ensure all new IT systems follow the open data, content, and web API policy and operationalize agency.gov/developer pages',
				'due' => '12 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
				),
			)),

		new DGS_Action_Item( array(
				'id' => '1.2.1',
				'parent' => '1.2',
				'text' => 'Document policy for architecting new IT systems for openness by default',
				'due' => '6 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'description',
							'label' => 'Describe implementation',
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '1.2.3',
				'parent' => '1.2',
				'text' => 'Operationalize Enterprise Data Inventory',
				'due' => '17 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'text',
							'name' => 'datasets-in-inventory',
							'label' => 'Number of datasets in the Enterprise Data Inventory',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'systems-in-inventory',
							'label' => 'Number of systems whose datasets are in the Enterprise Data Inventory',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'datasets-potential-public',
							'label' => 'Number of datasets that can be made publicly available',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'datasets-listed',
							'label' => 'Number of datasets listed on the /data page',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'datasets-in-inventory',
							'label' => 'Number of released datasets listed on the /data page',
						)),
				),
			)),

		//2.2
		new DGS_Action_Item( array(
				'id' => '2.2',
				'text' => 'Make high-value data and content in at least two existing, major customer-facing systems available through web APIs, apply metadata tagging and publish a plan to transition additional high-value systems',
				'due' => '12 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '2.2.1',
				'parent' => '2.1',
				'text' => 'Publish plan on future activity',
				'due' => '12 months',
				'multiple' => false,
				'fields' => array(
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'describe',
							'label' => 'Describe Implementation',
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '2.2.2',
				'parent' => '2.1',
				'text' => 'Make 2+ systems (datasets) available via web APIs with metadata tags',
				'due' => '12 months',
				'multiple' => true,
				'fields' => array(
					new DGS_field( array(
							'type' => 'text',
							'name' => 'system',
							'label' => 'Name of system',
						)),
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'description',
							'label' => 'Description of system',
						)),
					new DGS_field( array(
							'type' => 'select',
							'name' => 'scope',
							'label' => 'Scope of system',
							'options' => array(
								new DGS_Option( 'internal', 'Internal' ),
								new DGS_Option( 'external', 'External' ),
								new DGS_Option( 'both', 'Both' ),
							),
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'customers',
							'label' => 'Main Customers',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'uii',
							'label' => 'Unique Investment Identifier',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'url',
							'label' => 'API Link',
						)),
				),
			)),

		//5.3
		new DGS_Action_Item( array(
				'id' => '5.3',
				'text' => 'Evaluate the government-wide contract vehicles in the alternatives analysis for all new mobile-related procurements',
				'due' => '12 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'policy',
							'label' => 'Describe Implementation',
						)),
				),
			)),

		//6.3
		new DGS_Action_Item( array(
				'id' => '6.3',
				'text' => 'Ensure all new digital services follow digital services and customer experience improvement guidelines',
				'due' => '12 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'policy',
							'label' => 'Describe Implementation',
						)),
				),
			)),

		//7.2
		new DGS_Action_Item( array(
				'id' => '7.2',
				'text' => 'Optimize at least two existing priority customer-facing services for mobile use and publish a plan for improving additional existing services',
				'due' => '12 months',
				'fields' => array(
					new DGS_field( array(
							'type' => 'select',
							'name' => 'status',
							'label' => 'Overall Status',
							'options' => array(
								new DGS_Option( 'not-started', 'Not Started' ),
								new DGS_Option( 'in-progress', 'In Progress' ),
								new DGS_Option( 'completed', 'Completed' ),
							)
						)),
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'policy',
							'label' => 'Describe Implementation',
						)),
				),
			)),
		new DGS_Action_Item( array(
				'id' => '7.2.1',
				'parent' => '7.2.',
				'text' => 'Report on services',
				'due' => '12 months',
				'multiple' => true,
				'fields' => array(
					new DGS_field( array(
							'type' => 'text',
							'name' => 'name',
							'label' => 'Service Name',
						)),
					new DGS_field( array(
							'type' => 'textarea',
							'name' => 'description',
							'label' => 'Service Description',
						)),
					new DGS_field( array(
							'type' => 'select',
							'name' => 'scope',
							'label' => 'System Scope',
							'options' => array(
								new DGS_Option( 'internal', 'Internal' ),
								new DGS_Option( 'external', 'External' ),
								new DGS_Option( 'both', 'Both' ),
							),
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'customers',
							'label' => 'Primary customers',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'uii',
							'label' => 'Unique Investment Identifier',
						)),
					new DGS_field( array(
							'type' => 'text',
							'name' => 'url',
							'label' => 'URL of service',
						)),
				),
			)),
	),
);

//