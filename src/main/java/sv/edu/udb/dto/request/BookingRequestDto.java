package sv.edu.udb.dto.request;

import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class BookingRequestDto {

    @NotNull(message = "El ID del evento es obligatorio")
    private Integer eventId;

    @NotNull(message = "La cantidad de tickets es obligatoria")
    @Positive(message = "La cantidad debe ser un número positivo")
    private Integer quantity;
}
