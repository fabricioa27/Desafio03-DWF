package sv.edu.udb.dto.request;

import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Pattern;
import jakarta.validation.constraints.Size;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@AllArgsConstructor
@NoArgsConstructor
public class UsuarioRequestDto {

    @NotBlank(message = "Debe ingresar un nombre de usuario")
    private String username;

    @Pattern(regexp = "^[^0-9]*$", message = "El campo no debe contener números")
    @NotBlank(message = "Ingrese sus nombres")
    @Size(min = 2, message = "Su nombre debe tener minimo 2 letras")
    private String firstname;

    @Pattern(regexp = "^[^0-9]*$", message = "El campo no debe contener números")
    @NotBlank(message = "Ingrese sus apellidos")
    @Size(min = 2, message = "Su apellido debe tener minimo 1 letras")
    private String lastname;

    @NotNull(message = "Debe ingresar su edad")
    @Min(value = 1, message = "La edad debe ser al menos 1")
    @Max(value = 120, message = "La edad no puede ser mayor a 120")
    private Integer age;

    @NotBlank(message =  "La contraseña es obligatoria")
    @Size(min = 8, message = "La contraseña debe tener al menos 8 caracteres")
    private String password;
}
