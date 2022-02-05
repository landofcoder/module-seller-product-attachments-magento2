<?php

declare(strict_types = 1);

/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lofmp_ProductAttachment
 * @copyright  Copyright (c) 2022 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lofmp\ProductAttachment\Plugin;

use LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use LizardMedia\ProductAttachment\Model\Attachment\Builder as AttachmentBuilder;
use Lof\MarketPlace\Controller\Marketplace\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class InitForSave
 * @package Lofmp\ProductAttachment\Plugin
 */
class InitForSave
{
    /**
     * @var AttachmentFactoryInterface
     */
    private $attachmentFactory;

    /**
     * @var AttachmentBuilder
     */
    private $attachmentBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param AttachmentFactoryInterface $attachmentFactory
     * @param AttachmentBuilder $attachmentBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        AttachmentFactoryInterface $attachmentFactory,
        AttachmentBuilder $attachmentBuilder,
        RequestInterface $request
    ) {
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentBuilder = $attachmentBuilder;
        $this->request = $request;
    }

    /**
     * @param Helper $subject
     * @param Product $product
     * @return Product
     * @throws LocalizedException
     */
    public function afterInitialize(Helper $subject, Product $product)
    {
        $downloadable = $this->request->getPost('downloadable');

        if (!empty($downloadable) && (isset($downloadable['attachment']) && is_array($downloadable['attachment']))) {
            $product->setDownloadableData($downloadable);

            $attachments = [];
            foreach ($downloadable['attachment'] as $attachmentData) {
                if (!$attachmentData ||
                    (isset($attachmentData['is_delete']) && (bool) $attachmentData['is_delete'])) {
                    continue;
                }

                $attachments[] = $this->attachmentBuilder->setData($attachmentData)
                    ->build($this->attachmentFactory->create());
            }

            $this->setProductAttachments($product, $attachments);
        } else {
            $this->setProductAttachments($product, null);
        }

        return $product;
    }

    /**
     * @param Product $product
     * @param array|null $attachments
     * @return void
     */
    private function setProductAttachments(Product $product, ?array $attachments): void
    {
        $extension = $product->getExtensionAttributes();
        $extension->setProductAttachments($attachments);
        $product->setExtensionAttributes($extension);
    }
}
