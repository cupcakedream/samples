// Load Dependencies
import { __ } from '@wordpress/i18n';
import blockData from './block.json';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button, TextareaControl, ToggleControl, 
    Card, CardHeader, CardBody } from '@wordpress/components';
import { plus, trash, arrowUp, arrowDown } from '@wordpress/icons';

// Setup Edit Script For Block
export default function Edit({ attributes, setAttributes }) {

    const { cards = [] } = attributes;

    // Get Fallbacks
    const defaultCards = blockData.attributes.cards.default;

    const updateCard = (index, field, value) => {
        const newCards = [...cards];
        newCards[index] = { ...newCards[index], [field]: value };
        setAttributes({ cards: newCards });
    };

    const addCard = () => {
        const newCard = blockData.example.attributes.cards[0]; 
        setAttributes({ cards: [...cards, newCard] });
    };

    const removeCard = (index) => {
        const newCards = cards.filter((_, i) => i !== index);
        setAttributes({ cards: newCards });
    };

    const moveCard = (index, direction) => {
        if ((direction === 'up' && index === 0) || 
            (direction === 'down' && index === cards.length - 1)) {
                return;
        }
        
        const newCards = [...cards];
        const newIndex = direction === 'up' ? index - 1 : index + 1;
        [newCards[index], newCards[newIndex]] = [newCards[newIndex], newCards[index]];
        setAttributes({ cards: newCards });
    };

    return (
        <>
            <InspectorControls>

                <PanelBody title={__('Cards', 'cc-blocks')} initialOpen={true}>
                    {Array.isArray(cards) && cards.map((card, index) => (
                        <Card key={index} className="cc-panel-card">
                            <CardHeader>
                                <div className='cc-panel-header'>
                                    <strong>{__('Card', 'cc-blocks')} {index + 1}</strong>
                                    <div>
                                        <Button 
                                            onClick={() => moveCard(index, 'up')} 
                                            icon={arrowUp} 
                                            size='small' 
                                            disabled={index === 0} />
                                        <Button 
                                            onClick={() => moveCard(index, 'down')} 
                                            icon={arrowDown} 
                                            size="small" 
                                            disabled={index === cards.length - 1} />
                                        <Button 
                                            onClick={() => removeCard(index)} 
                                            icon={trash} 
                                            size="small" 
                                            isDestructive />
                                    </div>
                                </div>
                            </CardHeader>
                            
                            <CardBody>
                                <TextControl 
                                    label={__('Title', 'cc-blocks')} 
                                    onChange={(value) => updateCard(index, 'cardTitle', value)} 
                                    value={card.cardTitle} 
                                />
                                <TextareaControl 
                                    label={__('Content', 'cc-blocks')} 
                                    onChange={(value) => updateCard(index, 'cardContent', value)} 
                                    value={card.cardContent} 
                                />
                                <div className="cc-panel-color">
                                    <label>{__('Background Color', 'cc-blocks')}</label>
                                    <input
                                        type="color"
                                        value={card.cardBackgroundColor || defaultCards[0].cardBackgroundColor}
                                        onChange={(e) => updateCard(index, 'cardBackgroundColor', e.target.value)}
                                        className="cc-panel-color-el"
                                    />
                                </div>
                                <div className="cc-panel-color">
                                    <label>{__('Card Text Color', 'cc-blocks')}</label>
                                    <input
                                        type="color"
                                        value={card.cardTextColor || defaultCards[0].cardTextColor}
                                        onChange={(e) => updateCard(index, 'cardTextColor', e.target.value)}
                                        className="cc-panel-color-el"
                                    />
                                </div>
                                <div className="cc-panel-color">
                                    <label>{__('Button Text', 'cc-blocks')}</label>
                                    <TextControl
                                        value={card.cardLink.text}
                                        onChange={(text) => updateCard(index, 'cardLink', { 
                                            ...card.cardLink, text 
                                        })}
                                        placeholder={__('Enter text...', 'cc-blocks')}
                                        className="cc-panel-color-el"
                                    />
                                    <label>{__('Button Link', 'cc-blocks')}</label>
                                    <TextControl
                                        value={card.cardLink.url}
                                        onChange={(url) => updateCard(index, 'cardLink', { 
                                            ...card.cardLink, url 
                                        })}
                                        placeholder={__('Enter URL...', 'cc-blocks')}
                                        className="cc-panel-color-el"
                                    />
                                    <ToggleControl
                                        label={__('Open in new tab', 'cc-blocks')}
                                        checked={card.cardLink.opensInNewTab}
                                        onChange={(opensInNewTab) => updateCard(index, 'cardLink', { 
                                            ...card.cardLink, opensInNewTab 
                                        })}
                                    />
                                </div>
                            </CardBody>
                        </Card>
                    ))}
                    
                    <Button
                        isPrimary
                        icon={plus}
                        onClick={addCard}
                        style={{ marginTop: '16px', width: '100%' }}
                    >
                        {__('Add Card', 'cc-blocks')}
                    </Button>
                </PanelBody>
            </InspectorControls>
            
            <div {...useBlockProps({ className: 'cc-carousel' })}>
                <div className="cc-wrap">
                    <div className="cc-items owl-carousel owl-theme">
                        {Array.isArray(cards) && cards.length > 0 ? cards.map((card, index) => (
                            <div 
                                key={index} 
                                className="cc-item" 
                                style={{ 
                                    backgroundColor: card.cardBackgroundColor, 
                                    color: card.cardTextColor 
                                }}>
                                <h4 className="cc-title">
                                    {card.cardTitle || defaultCards[0].cardTitle}
                                </h4>
                                <div className="cc-text">
                                    {card.cardContent || defaultCards[0].cardContent}
                                </div>
                                <a 
                                    className="cc-button" 
                                    href={card.cardLink.url || defaultCards[0].cardLink.url}
                                    target={card.cardLink.opensInNewTab ? "_blank" : "_self"}
                                >
                                    {card.cardLink.text || defaultCards[0].cardLink.text}
                                </a>
                            </div>
                        )) : (
                            <div className="cc-panel-none">
                                {__('No cards added yet. Use the inspector panel to add your first card.', 'cc-blocks')}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
